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

namespace PackageFactory\ComponentEngine\Test\Unit\Target\Php\Transpiler\BinaryOperation;

use PackageFactory\ComponentEngine\Parser\Ast\BinaryOperationNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\BinaryOperation\BinaryOperationTranspiler;
use PackageFactory\ComponentEngine\Test\Unit\Language\ASTNodeFixtures;
use PHPUnit\Framework\TestCase;

final class BinaryOperationTranspilerTest extends TestCase
{
    /**
     * @return array<string,mixed>
     */
    public static function binaryOperationExamples(): array
    {
        return [
            'true && false' => ['true && false', '(true && false)'],
            'a && false' => ['a && false', '($this->a && false)'],
            'true && b' => ['true && b', '(true && $this->b)'],
            'a && b' => ['a && b', '($this->a && $this->b)'],
            'true || false' => ['true || false', '(true || false)'],
            'a || false' => ['a || false', '($this->a || false)'],
            'true || b' => ['true || b', '(true || $this->b)'],
            'a || b' => ['a || b', '($this->a || $this->b)'],
            'true && "foo"' => ['true && "foo"', '(true && \'foo\')'],
            'true || "foo"' => ['true || "foo"', '(true || \'foo\')'],
            'true && 42' => ['true && 42', '(true && 42)'],
            'true || 42' => ['true || 42', '(true || 42)'],

            '4 === 2' => ['4 === 2', '(4 === 2)'],
            'a === 2' => ['a === 2', '($this->a === 2)'],
            '4 === b' => ['4 === b', '(4 === $this->b)'],
            'a === b' => ['a === b', '($this->a === $this->b)'],
            '4 !== 2' => ['4 !== 2', '(4 !== 2)'],
            'a !== 2' => ['a !== 2', '($this->a !== 2)'],
            '4 !== b' => ['4 !== b', '(4 !== $this->b)'],
            'a !== b' => ['a !== b', '($this->a !== $this->b)'],
            '4 > 2' => ['4 > 2', '(4 > 2)'],
            'a > 2' => ['a > 2', '($this->a > 2)'],
            '4 > b' => ['4 > b', '(4 > $this->b)'],
            'a > b' => ['a > b', '($this->a > $this->b)'],
            '4 >= 2' => ['4 >= 2', '(4 >= 2)'],
            'a >= 2' => ['a >= 2', '($this->a >= 2)'],
            '4 >= b' => ['4 >= b', '(4 >= $this->b)'],
            'a >= b' => ['a >= b', '($this->a >= $this->b)'],
            '4 < 2' => ['4 < 2', '(4 < 2)'],
            'a < 2' => ['a < 2', '($this->a < 2)'],
            '4 < b' => ['4 < b', '(4 < $this->b)'],
            'a < b' => ['a < b', '($this->a < $this->b)'],
            '4 <= 2' => ['4 <= 2', '(4 <= 2)'],
            'a <= 2' => ['a <= 2', '($this->a <= 2)'],
            '4 <= b' => ['4 <= b', '(4 <= $this->b)'],
            'a <= b' => ['a <= b', '($this->a <= $this->b)'],

            'true && true && true' => ['true && true && true', '((true && true) && true)'],
            '1 === 1 === true' => ['1 === 1 === true', '((1 === 1) === true)'],
        ];
    }

    /**
     * @dataProvider binaryOperationExamples
     * @test
     * @param string $binaryOperationAsString
     * @param string $expectedTranspilationResult
     * @return void
     */
    public function transpilesBinaryOperationNodes(string $binaryOperationAsString, string $expectedTranspilationResult): void
    {
        $binaryOperationTranspiler = new BinaryOperationTranspiler(
            scope: new DummyScope()
        );
        $binaryOperationNode = ASTNodeFixtures::BinaryOperation($binaryOperationAsString);

        $actualTranspilationResult = $binaryOperationTranspiler->transpile(
            $binaryOperationNode
        );

        $this->assertEquals(
            $expectedTranspilationResult,
            $actualTranspilationResult
        );
    }
}
