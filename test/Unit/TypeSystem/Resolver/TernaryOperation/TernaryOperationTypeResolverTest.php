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

namespace PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Resolver\TernaryOperation;

use PackageFactory\ComponentEngine\Test\Unit\Language\ASTNodeFixtures;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\TernaryOperation\TernaryOperationTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Type\IntegerType\IntegerType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PackageFactory\ComponentEngine\TypeSystem\Type\UnionType\UnionType;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;
use PHPUnit\Framework\TestCase;

final class TernaryOperationTypeResolverTest extends TestCase
{
    /**
     * @return array<string,mixed>
     */
    public static function ternaryOperationExamples(): array
    {
        return [
            'true ? 42 : "foo"' => ['true ? 42 : "foo"', IntegerType::get()],
            'false ? 42 : "foo"' => ['false ? 42 : "foo"', StringType::get()],
            '1 < 2 ? 42 : "foo"' => ['1 < 2 ? 42 : "foo"', UnionType::of(IntegerType::get(), StringType::get())],
            '1 < 2 ? variableOfTypeString : variableOfTypeNumber' => [
                '1 < 2 ? variableOfTypeString : variableOfTypeNumber',
                UnionType::of(IntegerType::get(), StringType::get())
            ]
        ];
    }

    /**
     * @dataProvider ternaryOperationExamples
     * @test
     * @param string $ternaryExpressionAsString
     * @param TypeInterface $expectedType
     * @return void
     */
    public function resolvesTernaryOperationToResultingType(string $ternaryExpressionAsString, TypeInterface $expectedType): void
    {
        $scope = new DummyScope(
            [StringType::get(), IntegerType::get()],
            [
                'variableOfTypeString' => StringType::get(),
                'variableOfTypeNumber' => IntegerType::get()
            ]
        );
        $ternaryOperationTypeResolver = new TernaryOperationTypeResolver(
            scope: $scope
        );
        $ternaryOperationNode = ASTNodeFixtures::TernaryOperation(
            $ternaryExpressionAsString
        );

        $actualType = $ternaryOperationTypeResolver->resolveTypeOf($ternaryOperationNode);

        $this->assertTrue(
            $expectedType->is($actualType),
            sprintf('Expected %s, got %s', $expectedType::class, $actualType::class)
        );
    }
}
