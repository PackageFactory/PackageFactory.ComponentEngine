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

namespace PackageFactory\ComponentEngine\Test\Unit\Target\Php\Transpiler\TernaryOperation;

use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\TernaryOperationNode;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\TernaryOperation\TernaryOperationTranspiler;
use PHPUnit\Framework\TestCase;

final class TernaryOperationTranspilerTest extends TestCase
{
    /**
     * @return array<string,mixed>
     */
    public function ternaryOperationExamples(): array
    {
        return [
            'true ? 42 : "foo"' => ['true ? 42 : "foo"', '(true ? 42 : \'foo\')'],
            'a ? 42 : "foo"' => ['a ? 42 : "foo"', '($this->a ? 42 : \'foo\')'],
            'true ? b : "foo"' => ['true ? b : "foo"', '(true ? $this->b : \'foo\')'],
            'true ? 42 : c' => ['true ? 42 : c', '(true ? 42 : $this->c)'],
            'a ? b : c' => ['a ? b : c', '($this->a ? $this->b : $this->c)'],
            'false ? 42 : "foo"' => ['false ? 42 : "foo"', '(false ? 42 : \'foo\')'],
            '1 < 2 ? 42 : "foo"' => ['1 < 2 ? 42 : "foo"', '((1 < 2) ? 42 : \'foo\')']
        ];
    }

    /**
     * @dataProvider ternaryOperationExamples
     * @test
     * @param string $ternaryOperationAsString
     * @param string $expectedTranspilationResult
     * @return void
     */
    public function transpilesTernaryOperationNodes(string $ternaryOperationAsString, string $expectedTranspilationResult): void
    {
        $ternaryOperationTranspiler = new TernaryOperationTranspiler(
            scope: new DummyScope()
        );
        $ternaryOperationNode = ExpressionNode::fromString($ternaryOperationAsString)->root;
        assert($ternaryOperationNode instanceof TernaryOperationNode);

        $actualTranspilationResult = $ternaryOperationTranspiler->transpile(
            $ternaryOperationNode
        );

        $this->assertEquals(
            $expectedTranspilationResult,
            $actualTranspilationResult
        );
    }
}