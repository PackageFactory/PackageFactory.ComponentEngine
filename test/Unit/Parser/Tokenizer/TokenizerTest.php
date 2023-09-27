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

namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Tokenizer;

use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;
use PHPUnit\Framework\TestCase;

final class TokenizerTest extends TestCase
{
    /**
     * @test
     */
    public function tokenizesEmptySourceToEmptyIterator(): void
    {
        $source = Source::fromString('');
        $tokenizer = Tokenizer::fromSource($source);
        $iterator = $tokenizer->getIterator();

        $this->assertFalse($iterator->valid());
    }

    /**
     * @test
     */
    public function tokenizesOpeningTag(): void
    {
        $source = Source::fromString('<a>');
        $tokenizer = Tokenizer::fromSource($source);
        $tokens = \iterator_to_array($tokenizer->getIterator(), false);

        $this->assertEquals(TokenType::TAG_START_OPENING, $tokens[0]->type);
        $this->assertEquals(TokenType::STRING, $tokens[1]->type);
        $this->assertEquals(TokenType::TAG_END, $tokens[2]->type);
    }

    /**
     * @test
     */
    public function tokenizesClosingTag(): void
    {
        $source = Source::fromString('</a>');
        $tokenizer = Tokenizer::fromSource($source);
        $tokens = \iterator_to_array($tokenizer->getIterator(), false);

        $this->assertEquals(TokenType::TAG_START_CLOSING, $tokens[0]->type);
        $this->assertEquals(TokenType::STRING, $tokens[1]->type);
        $this->assertEquals(TokenType::TAG_END, $tokens[2]->type);
    }

    /**
     * @test
     */
    public function tokenizesMultipleBracketedStatements(): void
    {
        $source = Source::fromString('(a ? b : c) ? (d ? e : f) : (g ? h : i)');
        $tokenizer = Tokenizer::fromSource($source);
        $tokens = \iterator_to_array($tokenizer->getIterator(), false);

        $this->assertEquals(TokenType::BRACKET_ROUND_OPEN, $tokens[0]->type);

        $this->assertEquals(TokenType::STRING, $tokens[1]->type);
        $this->assertEquals('a', $tokens[1]->value);

        $this->assertEquals(TokenType::SPACE, $tokens[2]->type);

        $this->assertEquals(TokenType::QUESTIONMARK, $tokens[3]->type);

        $this->assertEquals(TokenType::SPACE, $tokens[4]->type);

        $this->assertEquals(TokenType::STRING, $tokens[5]->type);
        $this->assertEquals('b', $tokens[5]->value);

        $this->assertEquals(TokenType::SPACE, $tokens[6]->type);

        $this->assertEquals(TokenType::COLON, $tokens[7]->type);

        $this->assertEquals(TokenType::SPACE, $tokens[8]->type);

        $this->assertEquals(TokenType::STRING, $tokens[9]->type);
        $this->assertEquals('c', $tokens[9]->value);

        $this->assertEquals(TokenType::BRACKET_ROUND_CLOSE, $tokens[10]->type);

        $this->assertEquals(TokenType::SPACE, $tokens[11]->type);

        $this->assertEquals(TokenType::QUESTIONMARK, $tokens[12]->type);

        $this->assertEquals(TokenType::SPACE, $tokens[13]->type);

        $this->assertEquals(TokenType::BRACKET_ROUND_OPEN, $tokens[14]->type);

        $this->assertEquals(TokenType::STRING, $tokens[15]->type);
        $this->assertEquals('d', $tokens[15]->value);

        $this->assertEquals(TokenType::SPACE, $tokens[16]->type);

        $this->assertEquals(TokenType::QUESTIONMARK, $tokens[17]->type);

        $this->assertEquals(TokenType::SPACE, $tokens[18]->type);

        $this->assertEquals(TokenType::STRING, $tokens[19]->type);
        $this->assertEquals('e', $tokens[19]->value);

        $this->assertEquals(TokenType::SPACE, $tokens[20]->type);

        $this->assertEquals(TokenType::COLON, $tokens[21]->type);

        $this->assertEquals(TokenType::SPACE, $tokens[22]->type);

        $this->assertEquals(TokenType::STRING, $tokens[23]->type);
        $this->assertEquals('f', $tokens[23]->value);

        $this->assertEquals(TokenType::BRACKET_ROUND_CLOSE, $tokens[24]->type);

        $this->assertEquals(TokenType::SPACE, $tokens[25]->type);

        $this->assertEquals(TokenType::COLON, $tokens[26]->type);

        $this->assertEquals(TokenType::SPACE, $tokens[27]->type);

        $this->assertEquals(TokenType::BRACKET_ROUND_OPEN, $tokens[28]->type);

        $this->assertEquals(TokenType::STRING, $tokens[29]->type);
        $this->assertEquals('g', $tokens[29]->value);

        $this->assertEquals(TokenType::SPACE, $tokens[30]->type);

        $this->assertEquals(TokenType::QUESTIONMARK, $tokens[31]->type);

        $this->assertEquals(TokenType::SPACE, $tokens[32]->type);

        $this->assertEquals(TokenType::STRING, $tokens[33]->type);
        $this->assertEquals('h', $tokens[33]->value);

        $this->assertEquals(TokenType::SPACE, $tokens[34]->type);

        $this->assertEquals(TokenType::COLON, $tokens[35]->type);

        $this->assertEquals(TokenType::SPACE, $tokens[36]->type);

        $this->assertEquals(TokenType::STRING, $tokens[37]->type);
        $this->assertEquals('i', $tokens[37]->value);

        $this->assertEquals(TokenType::BRACKET_ROUND_CLOSE, $tokens[38]->type);
    }
}
