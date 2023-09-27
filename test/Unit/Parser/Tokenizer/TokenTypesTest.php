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

namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Tokenizer;

use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenTypes;
use PHPUnit\Framework\TestCase;

final class TokenTypesTest extends TestCase
{
    /**
     * @test
     */
    public function providesDebugStringForSingleItem(): void
    {
        $tokenTypes = TokenTypes::from(TokenType::COLON);

        $this->assertEquals(
            'COLON (":")',
            $tokenTypes->toDebugString()
        );
    }

    /**
     * @test
     */
    public function providesDebugStringForTwoItems(): void
    {
        $tokenTypes = TokenTypes::from(TokenType::PERIOD, TokenType::COMMA);

        $this->assertEquals(
            'PERIOD (".") or COMMA (",")',
            $tokenTypes->toDebugString()
        );
    }

    /**
     * @test
     */
    public function providesDebugStringForThreeOrMoreItems(): void
    {
        $tokenTypes = TokenTypes::from(
            TokenType::PERIOD,
            TokenType::COMMA,
            TokenType::COLON,
            TokenType::DOLLAR
        );

        $this->assertEquals(
            'PERIOD ("."), COMMA (","), COLON (":") or DOLLAR ("$")',
            $tokenTypes->toDebugString()
        );
    }

    /**
     * @test
     */
    public function containsReturnsTrueIfCollectionContainsGivenTokenType(): void
    {
        $tokenTypes = TokenTypes::from(
            TokenType::PERIOD,
            TokenType::COMMA,
            TokenType::COLON,
            TokenType::DOLLAR
        );

        $this->assertTrue($tokenTypes->contains(TokenType::PERIOD));
        $this->assertTrue($tokenTypes->contains(TokenType::COMMA));
        $this->assertTrue($tokenTypes->contains(TokenType::COLON));
        $this->assertTrue($tokenTypes->contains(TokenType::DOLLAR));
    }

    /**
     * @test
     */
    public function containsReturnsFalseIfCollectionDoesNotContainGivenTokenType(): void
    {
        $tokenTypes = TokenTypes::from(
            TokenType::PERIOD,
            TokenType::COMMA,
            TokenType::COLON,
            TokenType::DOLLAR
        );

        $this->assertFalse($tokenTypes->contains(TokenType::SLASH_FORWARD));
        $this->assertFalse($tokenTypes->contains(TokenType::COMMENT));
        $this->assertFalse($tokenTypes->contains(TokenType::STRING));
        $this->assertFalse($tokenTypes->contains(TokenType::EQUALS));
    }
}
