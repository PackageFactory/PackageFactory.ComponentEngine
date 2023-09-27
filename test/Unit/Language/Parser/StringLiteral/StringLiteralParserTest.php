<?php

/**
 * PackageFactory.ComponentEngine - Universal View Components for PHP
 *   Copyright (C) 2023 Contributors of PackageFactory.ComponentEngine
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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Parser\StringLiteral;

use PackageFactory\ComponentEngine\Language\AST\Node\StringLiteral\StringLiteralNode;
use PackageFactory\ComponentEngine\Language\Lexer\Lexer;
use PackageFactory\ComponentEngine\Language\Parser\StringLiteral\StringLiteralParser;
use PackageFactory\ComponentEngine\Test\Unit\Language\Parser\ParserTestCase;

final class StringLiteralParserTest extends ParserTestCase
{
    /**
     * @test
     */
    public function parsesEmptyString(): void
    {
        $stringLiteralParser = StringLiteralParser::singleton();
        $lexer = new Lexer('""');

        $expectedStringLiteralNode = new StringLiteralNode(
            rangeInSource: $this->range([0, 0], [0, 1]),
            value: ''
        );

        $this->assertEquals(
            $expectedStringLiteralNode,
            $stringLiteralParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesString(): void
    {
        $stringLiteralParser = StringLiteralParser::singleton();
        $lexer = new Lexer('"Hello World"');

        $expectedStringLiteralNode = new StringLiteralNode(
            rangeInSource: $this->range([0, 0], [0, 12]),
            value: 'Hello World'
        );

        $this->assertEquals(
            $expectedStringLiteralNode,
            $stringLiteralParser->parse($lexer)
        );
    }
}
