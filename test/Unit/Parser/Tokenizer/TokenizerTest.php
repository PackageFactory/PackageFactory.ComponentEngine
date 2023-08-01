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
}
