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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Parser\IntegerLiteral;

use PackageFactory\ComponentEngine\Language\AST\Node\IntegerLiteral\IntegerFormat;
use PackageFactory\ComponentEngine\Language\AST\Node\IntegerLiteral\IntegerLiteralNode;
use PackageFactory\ComponentEngine\Language\Parser\IntegerLiteral\IntegerLiteralCouldNotBeParsed;
use PackageFactory\ComponentEngine\Language\Parser\IntegerLiteral\IntegerLiteralParser;
use PackageFactory\ComponentEngine\Parser\Source\Path;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenTypes;
use PackageFactory\ComponentEngine\Test\Unit\Language\Parser\ParserTestCase;

final class IntegerLiteralParserTest extends ParserTestCase
{
    /**
     * @test
     */
    public function binaryInteger(): void
    {
        $integerLiteralParser = new IntegerLiteralParser();
        $tokens = $this->createTokenIterator('0b1010110101');

        $expectedIntegerLiteralNode = new IntegerLiteralNode(
            rangeInSource: $this->range([0, 0], [0, 11]),
            format: IntegerFormat::BINARY,
            value: '0b1010110101'
        );

        $this->assertEquals(
            $expectedIntegerLiteralNode,
            $integerLiteralParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function octalInteger(): void
    {
        $integerLiteralParser = new IntegerLiteralParser();
        $tokens = $this->createTokenIterator('0o755');

        $expectedIntegerLiteralNode = new IntegerLiteralNode(
            rangeInSource: $this->range([0, 0], [0, 4]),
            format: IntegerFormat::OCTAL,
            value: '0o755'
        );

        $this->assertEquals(
            $expectedIntegerLiteralNode,
            $integerLiteralParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function decimalInteger(): void
    {
        $integerLiteralParser = new IntegerLiteralParser();
        $tokens = $this->createTokenIterator('1234567890');

        $expectedIntegerLiteralNode = new IntegerLiteralNode(
            rangeInSource: $this->range([0, 0], [0, 9]),
            format: IntegerFormat::DECIMAL,
            value: '1234567890'
        );

        $this->assertEquals(
            $expectedIntegerLiteralNode,
            $integerLiteralParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function hexadecimalInteger(): void
    {
        $integerLiteralParser = new IntegerLiteralParser();
        $tokens = $this->createTokenIterator('0x123456789ABCDEF');

        $expectedIntegerLiteralNode = new IntegerLiteralNode(
            rangeInSource: $this->range([0, 0], [0, 16]),
            format: IntegerFormat::HEXADECIMAL,
            value: '0x123456789ABCDEF'
        );

        $this->assertEquals(
            $expectedIntegerLiteralNode,
            $integerLiteralParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function throwsIfTokenStreamsEndsUnexpectedly(): void
    {
        $this->assertThrowsParserException(
            function () {
                $integerLiteralParser = new IntegerLiteralParser();
                $tokens = $this->createTokenIterator('');

                $integerLiteralParser->parse($tokens);
            },
            IntegerLiteralCouldNotBeParsed::becauseOfUnexpectedEndOfFile()
        );
    }

    /**
     * @test
     */
    public function throwsIfUnexpectedTokenIsEncountered(): void
    {
        $this->assertThrowsParserException(
            function () {
                $integerLiteralParser = new IntegerLiteralParser();
                $tokens = $this->createTokenIterator('foo1234');

                $integerLiteralParser->parse($tokens);
            },
            IntegerLiteralCouldNotBeParsed::becauseOfUnexpectedToken(
                expectedTokenTypes: TokenTypes::from(
                    TokenType::NUMBER_BINARY,
                    TokenType::NUMBER_OCTAL,
                    TokenType::NUMBER_DECIMAL,
                    TokenType::NUMBER_HEXADECIMAL
                ),
                actualToken: new Token(
                    type: TokenType::STRING,
                    value: 'foo1234',
                    boundaries: $this->range([0, 0], [0, 6]),
                    sourcePath: Path::createMemory()
                )
            )
        );
    }
}
