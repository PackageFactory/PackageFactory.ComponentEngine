<?php

/**
 * PackageFactory.ComponentEngine - Universal View Components for PHP
 *   Copyright (C) 2022 Contributors of PackageFactory.ComponentEngine
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Resolver\Match;

use PackageFactory\ComponentEngine\Module\ModuleId;
use PackageFactory\ComponentEngine\Test\Unit\Language\ASTNodeFixtures;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\Match\MatchTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Type\BooleanType\BooleanType;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumStaticType;
use PackageFactory\ComponentEngine\TypeSystem\Type\IntegerType\IntegerType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PackageFactory\ComponentEngine\TypeSystem\Type\UnionType\UnionType;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;
use PHPUnit\Framework\TestCase;

final class MatchTypeResolverTest extends TestCase
{
    /**
     * @return array<string,mixed>
     */
    public static function matchExamples(): array
    {
        return [
            'match (true) { true -> 42 false -> "foo" }' => [
                'match (true) { true -> 42 false -> "foo" }',
                IntegerType::singleton()
            ],
            'match (false) { true -> 42 false -> "foo" }' => [
                'match (false) { true -> 42 false -> "foo" }',
                StringType::singleton()
            ],
            'match (variableOfTypeBoolean) { true -> 42 false -> "foo" }' => [
                'match (variableOfTypeBoolean) { true -> 42 false -> "foo" }',
                UnionType::of(IntegerType::singleton(), StringType::singleton())
            ],
            'match (variableOfTypeBoolean) { true -> variableOfTypeNumber false -> variableOfTypeString }' => [
                'match (variableOfTypeBoolean) { true -> variableOfTypeNumber false -> variableOfTypeString }',
                UnionType::of(IntegerType::singleton(), StringType::singleton())
            ],
            'match enum with all declared members' => [
                <<<EOF
                match (someEnumValue) {
                    SomeEnum.A -> variableOfTypeNumber
                    SomeEnum.B -> variableOfTypeString
                    SomeEnum.C -> variableOfTypeBoolean
                }
                EOF,
                UnionType::of(IntegerType::singleton(), StringType::singleton(), BooleanType::singleton())
            ],
            'match enum with some declared members and default' => [
                <<<EOF
                match (someEnumValue) {
                    SomeEnum.A -> variableOfTypeNumber
                    SomeEnum.B -> variableOfTypeString
                    default -> variableOfTypeBoolean
                }
                EOF,
                UnionType::of(IntegerType::singleton(), StringType::singleton(), BooleanType::singleton())
            ],
        ];
    }

    /**
     * @dataProvider matchExamples
     * @test
     * @param string $matchAsString
     * @param TypeInterface $expectedType
     * @return void
     */
    public function resolvesMatchToResultingType(string $matchAsString, TypeInterface $expectedType): void
    {
        $scope = new DummyScope(
            [
                BooleanType::singleton(),
                StringType::singleton(),
                IntegerType::singleton(),
                $someStaticEnumType = EnumStaticType::fromModuleIdAndDeclaration(
                    ModuleId::fromString("module-a"),
                    ASTNodeFixtures::EnumDeclaration(
                        'enum SomeEnum { A B C }'
                    )
                )
            ],
            [
                'variableOfTypeBoolean' => BooleanType::singleton(),
                'variableOfTypeString' => StringType::singleton(),
                'variableOfTypeNumber' => IntegerType::singleton(),
                'someEnumValue' => $someStaticEnumType->toEnumInstanceType(),
                'SomeEnum' => $someStaticEnumType
            ]
        );
        $matchTypeResolver = new MatchTypeResolver(scope: $scope);
        $matchNode = ASTNodeFixtures::Match($matchAsString);

        $actualType = $matchTypeResolver->resolveTypeOf($matchNode);

        $this->assertTrue(
            $expectedType->is($actualType),
            sprintf('Expected %s, got %s', $expectedType::class, $actualType::class)
        );
    }

    /**
     * @return iterable<mixed>
     */
    public static function malformedEnumExamples(): iterable
    {
        yield "Missing match" => [
            <<<EOF
            match (someEnumValue) {
                SomeEnum.A -> "a"
                SomeEnum.B -> "a"
            }
            EOF,
            "@TODO Error: member C not checked"
        ];

        yield "Non existent enum member access" => [
            <<<EOF
            match (someEnumValue) {
                SomeEnum.A -> "a"
                SomeEnum.B -> "a"
                SomeEnum.C -> "a"
                SomeEnum.NonExistent -> "a"
            }
            EOF,
            "@TODO cannot access member NonExistent of enum SomeEnum"
        ];

        yield "Duplicate match 1" => [
            <<<EOF
            match (someEnumValue) {
                SomeEnum.A -> "a"
                SomeEnum.A -> "a"
            }
            EOF,
            "@TODO Error: Enum path A was already defined once in this match and cannot be used twice"
        ];

        yield "Duplicate match 2" => [
            <<<EOF
            match (someEnumValue) {
                SomeEnum.A, SomeEnum.A -> "a"
            }
            EOF,
            "@TODO Error: Enum path A was already defined once in this match and cannot be used twice"
        ];

        yield "Incompatible enum types" => [
            <<<EOF
            match (someEnumValue) {
                OtherEnum.A -> "a"
            }
            EOF,
            "@TODO Error: incompatible enum match: got OtherEnum expected SomeEnum"
        ];

        yield "Cant match enum and string" => [
            <<<EOF
            match (someEnumValue) {
                "foo" -> "a"
            }
            EOF,
            "@TODO Error: Cannot match enum with type of PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType"
        ];

        yield "Matching enum value should be referenced statically" => [
            <<<EOF
            match (someEnumValue) {
                someEnumValue -> "a"
            }
            EOF,
            '@TODO Error: Matching enum value should be referenced statically'
        ];
    }

    /**
     * @dataProvider malformedEnumExamples
     * @test
     */
    public function malformedMatchCannotBeResolved(string $matchAsString, string $expectedErrorMessage): void
    {
        $this->expectExceptionMessage($expectedErrorMessage);
        $scope = new DummyScope(
            [
                $someStaticEnumType = EnumStaticType::fromModuleIdAndDeclaration(
                    ModuleId::fromString("module-a"),
                    ASTNodeFixtures::EnumDeclaration(
                        'enum SomeEnum { A B C }'
                    )
                ),
                $otherStaticEnumType = EnumStaticType::fromModuleIdAndDeclaration(
                    ModuleId::fromString("module-a"),
                    ASTNodeFixtures::EnumDeclaration('enum OtherEnum { A }')
                )
            ],
            [
                'SomeEnum' =>  $someStaticEnumType,
                'someEnumValue' =>  $someStaticEnumType->toEnumInstanceType(),
                'OtherEnum' => $otherStaticEnumType
            ]
        );

        $matchTypeResolver = new MatchTypeResolver(
            scope: $scope
        );
        $matchNode = ASTNodeFixtures::Match($matchAsString);

        $matchTypeResolver->resolveTypeOf($matchNode);
    }
}
