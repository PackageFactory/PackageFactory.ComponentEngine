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

namespace PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Resolver\NumberLiteral;

use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\IntegerLiteralNode;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\IntegerLiteral\IntegerLiteralTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Type\IntegerType\IntegerType;
use PHPUnit\Framework\TestCase;

final class IntegerLiteralTypeResolverTest extends TestCase
{
    /**
     * @test
     */
    public function resolvesIntegerLiteralToIntegerType(): void
    {
        $integerLiteralTypeResolver = new IntegerLiteralTypeResolver();
        $integerLiteralNode = ExpressionNode::fromString('42')->root;
        assert($integerLiteralNode instanceof IntegerLiteralNode);

        $expectedType = IntegerType::get();
        $actualType = $integerLiteralTypeResolver->resolveTypeOf($integerLiteralNode);

        $this->assertTrue(
            $expectedType->is($actualType),
            sprintf('Expected %s, got %s', $expectedType::class, $actualType::class)
        );
    }
}