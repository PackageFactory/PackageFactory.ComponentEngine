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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Parser\StringLiteral;

use PackageFactory\ComponentEngine\Language\Parser\StringLiteral\StringLiteralParser;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Tokenizer;
use PHPUnit\Framework\TestCase;

final class StringLiteralParserTest extends TestCase
{
    /**
     * @test
     */
    public function producesStringLiteralNodeForLiteralString(): void
    {
        $stringLiteralParser = new StringLiteralParser();

        $tokens = Tokenizer::fromSource(Source::fromString('"Hello World"'))->getIterator();
        $stringLiteralNode = $stringLiteralParser->parse($tokens);

        $this->assertEquals('Hello World', $stringLiteralNode->value);
        $this->assertEquals(':memory:', $stringLiteralNode->location->sourcePath);
        $this->assertEquals(1, $stringLiteralNode->location->boundaries->start->index);
        $this->assertEquals(0, $stringLiteralNode->location->boundaries->start->rowIndex);
        $this->assertEquals(1, $stringLiteralNode->location->boundaries->start->columnIndex);
        $this->assertEquals(11, $stringLiteralNode->location->boundaries->end->index);
        $this->assertEquals(0, $stringLiteralNode->location->boundaries->end->rowIndex);
        $this->assertEquals(11, $stringLiteralNode->location->boundaries->end->columnIndex);
    }
}
