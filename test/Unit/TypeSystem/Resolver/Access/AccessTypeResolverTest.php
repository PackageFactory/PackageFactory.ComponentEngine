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

namespace PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Resolver\Access;

use PackageFactory\ComponentEngine\Domain\EnumMemberName\EnumMemberName;
use PackageFactory\ComponentEngine\Module\ModuleId;
use PackageFactory\ComponentEngine\Test\Unit\Language\ASTNodeFixtures;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\Access\AccessTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumInstanceType;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumStaticType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;
use PHPUnit\Framework\TestCase;

final class AccessTypeResolverTest extends TestCase
{
    /**
     * @return iterable<mixed>
     */
    public static function invalidAccessExamples(): iterable
    {
        yield 'access property on primitive string' => [
            'someString.bar',
            '@TODO Error: Cannot access on type PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType'
        ];

        yield 'access invalid property on enum' => [
            'SomeEnum.NonExistent',
            '@TODO cannot access member NonExistent of enum SomeEnum'
        ];

        yield "access enum member on non static enum instance" => [
            'someEnumValue.A',
            "@TODO Error: Cannot access on type PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumInstanceType"
        ];
    }

    private function resolveAccessType(string $accessAsString, ScopeInterface $scope): TypeInterface
    {
        $accessTypeResolver = new AccessTypeResolver(scope: $scope);
        $accessNode = ASTNodeFixtures::Access($accessAsString);

        return $accessTypeResolver->resolveTypeOf($accessNode);
    }

    /**
     * @test
     */
    public function enumMemberAccessOnStaticEnum(): void
    {
        $scope = new DummyScope(
            [
                $someEnum = EnumStaticType::fromModuleIdAndDeclaration(
                    ModuleId::fromString("module-a"),
                    ASTNodeFixtures::EnumDeclaration(
                        'enum SomeEnum { A("Hi") }'
                    )
                )
            ],
            ['SomeEnum' => $someEnum]
        );

        $accessType = $this->resolveAccessType(
            'SomeEnum.A',
            $scope
        );

        $this->assertInstanceOf(EnumInstanceType::class, $accessType);
        assert($accessType instanceof EnumInstanceType);

        $this->assertTrue($accessType->enumStaticType->is($someEnum));

        $this->assertEquals(EnumMemberName::from('A'), $accessType->getMemberName());
    }

    /**
     * @dataProvider invalidAccessExamples
     * @test
     */
    public function invalidAccessResultsInError(string $accessAsString, string $expectedErrorMessage): void
    {
        $this->expectExceptionMessage($expectedErrorMessage);

        $scope = new DummyScope(
            [
                StringType::singleton(),
                $someEnum = EnumStaticType::fromModuleIdAndDeclaration(
                    ModuleId::fromString("module-a"),
                    ASTNodeFixtures::EnumDeclaration(
                        'enum SomeEnum { A }'
                    )
                )
            ],
            [
                'someString' => StringType::singleton(),
                'SomeEnum' => $someEnum,
                'someEnumValue' => $someEnum->toEnumInstanceType()
            ]
        );
        $accessTypeResolver = new AccessTypeResolver(scope: $scope);
        $accessNode = ASTNodeFixtures::Access($accessAsString);

        $accessTypeResolver->resolveTypeOf($accessNode);
    }
}
