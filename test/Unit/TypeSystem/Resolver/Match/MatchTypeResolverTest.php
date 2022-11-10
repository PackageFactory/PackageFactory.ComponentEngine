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

use PackageFactory\ComponentEngine\Parser\Ast\EnumDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\Match\MatchTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Type\BooleanType\BooleanType;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumType;
use PackageFactory\ComponentEngine\TypeSystem\Type\NumberType\NumberType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PackageFactory\ComponentEngine\TypeSystem\Type\UnionType\UnionType;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;
use PHPUnit\Framework\TestCase;

final class MatchTypeResolverTest extends TestCase
{
    public function matchExamples(): array
    {
        return [
            'match (true) { true -> 42 false -> "foo" }' => [
                'match (true) { true -> 42 false -> "foo" }', 
                NumberType::get()
            ],
            'match (false) { true -> 42 false -> "foo" }' => [
                'match (false) { true -> 42 false -> "foo" }',
                StringType::get()
            ],
            'match (variableOfTypeBoolean) { true -> 42 false -> "foo" }' => [
                'match (variableOfTypeBoolean) { true -> 42 false -> "foo" }',
                UnionType::of(NumberType::get(), StringType::get())
            ],
            'match (variableOfTypeBoolean) { true -> variableOfTypeNumber false -> variableOfTypeString }' => [
                'match (variableOfTypeBoolean) { true -> variableOfTypeNumber false -> variableOfTypeString }',
                UnionType::of(NumberType::get(), StringType::get())
            ],
            'match (someEnumValue) { SomeEnum.A -> variableOfTypeNumber SomeEnum.B -> variableOfTypeString SomeEnum.C -> variableOfTypeBoolean }' => [
                'match (someEnumValue) { SomeEnum.A -> variableOfTypeNumber SomeEnum.B -> variableOfTypeString SomeEnum.C -> variableOfTypeBoolean }',
                UnionType::of(NumberType::get(), StringType::get(), BooleanType::get())
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
        $someEnumType = EnumType::fromEnumDeclarationNode(
            EnumDeclarationNode::fromString(
                'enum SomeEnum { A B C }'
            )
        );
        $scope = new DummyScope([
            'variableOfTypeBoolean' => BooleanType::get(),
            'variableOfTypeString' => StringType::get(),
            'variableOfTypeNumber' => NumberType::get(),
            'someEnumValue' => $someEnumType
        ]);
        $matchTypeResolver = new MatchTypeResolver(
            scope: $scope
        );
        $matchNode = ExpressionNode::fromString($matchAsString)->root;

        $actualType = $matchTypeResolver->resolveTypeOf($matchNode);

        $this->assertTrue(
            $expectedType->is($actualType),
            sprintf('Expected %s, got %s', $expectedType::class, $actualType::class)
        );
    }
}
