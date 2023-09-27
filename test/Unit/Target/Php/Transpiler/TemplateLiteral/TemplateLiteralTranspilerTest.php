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

namespace PackageFactory\ComponentEngine\Test\Unit\Target\Php\Transpiler\TemplateLiteral;

use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\TemplateLiteral\TemplateLiteralTranspiler;
use PackageFactory\ComponentEngine\Test\Unit\Language\ASTNodeFixtures;
use PHPUnit\Framework\TestCase;

final class TemplateLiteralTranspilerTest extends TestCase
{
    /**
     * @return iterable<mixed>
     */
    public static function templateLiteralExamples(): iterable
    {
        yield $source = <<<EOF
        """
        Hello World
        """
        EOF => [$source, '\'Hello World\''];

        yield $source = <<<EOF
        """
        Hello {name}
        """
        EOF => [$source, '\'Hello \' . $this->name'];

        yield $source = <<<EOF
        """
        {greeting} World
        """
        EOF => [$source, '$this->greeting . \' World\''];

        yield $source = <<<EOF
        """
        Hello {name}! How are you?
        """
        EOF => [$source, '\'Hello \' . $this->name . \'! How are you?\''];

        yield $source = <<<EOF
        """
        Hello {name}! {question}?
        """
        EOF => [$source, '\'Hello \' . $this->name . \'! \' . $this->question . \'?\''];
    }

    /**
     * @dataProvider templateLiteralExamples
     * @test
     * @param string $templateLiteralAsString
     * @param string $expectedTranspilationResult
     * @return void
     */
    public function transpilesTemplateLiteralNodes(string $templateLiteralAsString, string $expectedTranspilationResult): void
    {
        $templateLiteralTranspiler = new TemplateLiteralTranspiler(
            scope: new DummyScope()
        );
        $templateLiteralNode = ASTNodeFixtures::TemplateLiteral($templateLiteralAsString);

        $actualTranspilationResult = $templateLiteralTranspiler->transpile(
            $templateLiteralNode
        );

        $this->assertEquals(
            $expectedTranspilationResult,
            $actualTranspilationResult
        );
    }
}
