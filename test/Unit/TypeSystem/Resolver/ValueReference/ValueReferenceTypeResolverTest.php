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

namespace PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Resolver\ValueReference;

use PackageFactory\ComponentEngine\Test\Unit\Language\ASTNodeFixtures;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\ValueReference\ValueReferenceTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PHPUnit\Framework\TestCase;

final class ValueReferenceTypeResolverTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function resolvesKnownValueReferenceToItsType(): void
    {
        $scope = new DummyScope([StringType::get()], ['foo' => StringType::get()]);
        $identifierTypeResolver = new ValueReferenceTypeResolver(scope: $scope);
        $identifierNode = ASTNodeFixtures::ValueReference('foo');

        $expectedType = StringType::get();
        $actualType = $identifierTypeResolver->resolveTypeOf($identifierNode);

        $this->assertTrue(
            $expectedType->is($actualType),
            sprintf('Expected %s, got %s', $expectedType::class, $actualType::class)
        );
    }

    /**
     * @test
     * @return void
     */
    public function throwsIfGivenValueReferenceIsUnknown(): void
    {
        $scope = new DummyScope();
        $identifierTypeResolver = new ValueReferenceTypeResolver(scope: $scope);
        $identifierNode = ASTNodeFixtures::ValueReference('foo');

        $this->expectExceptionMessageMatches('/unknown identifier/i');

        $identifierTypeResolver->resolveTypeOf($identifierNode);
    }
}
