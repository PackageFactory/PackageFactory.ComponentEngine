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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Parser\NullLiteral;

use PackageFactory\ComponentEngine\Language\Parser\NullLiteral\NullLiteralParser;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Tokenizer;
use PHPUnit\Framework\TestCase;

final class NullLiteralParserTest extends TestCase
{
    /**
     * @test
     */
    public function producesAstNodeForNullIfGivenOneNullToken(): void
    {
        $nullLiteralParser = new NullLiteralParser();

        $tokens = Tokenizer::fromSource(Source::fromString('null'))->getIterator();
        $nullLiteralNode = $nullLiteralParser->parse($tokens);

        $this->assertEquals(':memory:', $nullLiteralNode->location->sourcePath);
        $this->assertEquals(0, $nullLiteralNode->location->boundaries->start->index);
        $this->assertEquals(0, $nullLiteralNode->location->boundaries->start->rowIndex);
        $this->assertEquals(0, $nullLiteralNode->location->boundaries->start->columnIndex);
        $this->assertEquals(3, $nullLiteralNode->location->boundaries->end->index);
        $this->assertEquals(0, $nullLiteralNode->location->boundaries->end->rowIndex);
        $this->assertEquals(3, $nullLiteralNode->location->boundaries->end->columnIndex);
    }
}
