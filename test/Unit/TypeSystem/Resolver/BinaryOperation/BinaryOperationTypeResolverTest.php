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

namespace PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Resolver\BinaryOperation;

use PackageFactory\ComponentEngine\Test\Unit\Language\ASTNodeFixtures;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\BinaryOperation\BinaryOperationTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Type\BooleanType\BooleanType;
use PackageFactory\ComponentEngine\TypeSystem\Type\IntegerType\IntegerType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PackageFactory\ComponentEngine\TypeSystem\Type\UnionType\UnionType;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;
use PHPUnit\Framework\TestCase;

final class BinaryOperationTypeResolverTest extends TestCase
{
    /**
     * @return array<string,mixed>
     */
    public static function binaryOperationExamples(): array
    {
        return [
            'true && false' => ['true && false', BooleanType::singleton()],
            'true || false' => ['true || false', BooleanType::singleton()],
            'true && "foo"' => ['true && "foo"', UnionType::of(BooleanType::singleton(), StringType::singleton())],
            'true || "foo"' => ['true || "foo"', UnionType::of(BooleanType::singleton(), StringType::singleton())],
            'true && 42' => ['true && 42', UnionType::of(BooleanType::singleton(), IntegerType::singleton())],
            'true || 42' => ['true || 42', UnionType::of(BooleanType::singleton(), IntegerType::singleton())],

            '4 === 2' => ['4 === 2', BooleanType::singleton()],
            '4 !== 2' => ['4 !== 2', BooleanType::singleton()],
            '4 > 2' => ['4 > 2', BooleanType::singleton()],
            '4 >= 2' => ['4 >= 2', BooleanType::singleton()],
            '4 < 2' => ['4 < 2', BooleanType::singleton()],
            '4 <= 2' => ['4 <= 2', BooleanType::singleton()],

            'true && true && true' => ['true && true && true', BooleanType::singleton()],
            '1 === 1 === true' => ['1 === 1 === true', BooleanType::singleton()],
        ];
    }

    /**
     * @dataProvider binaryOperationExamples
     * @test
     * @param string $binaryOperationAsString
     * @param TypeInterface $expectedType
     * @return void
     */
    public function resolvesBinaryOperationToResultingType(string $binaryOperationAsString, TypeInterface $expectedType): void
    {
        $scope = new DummyScope();
        $binaryOperationTypeResolver = new BinaryOperationTypeResolver(
            scope: $scope
        );
        $binaryOperationNode = ASTNodeFixtures::BinaryOperation($binaryOperationAsString);

        $actualType = $binaryOperationTypeResolver->resolveTypeOf($binaryOperationNode);

        $this->assertTrue(
            $expectedType->is($actualType),
            sprintf('Expected %s, got %s', $expectedType::class, $actualType::class)
        );
    }
}
