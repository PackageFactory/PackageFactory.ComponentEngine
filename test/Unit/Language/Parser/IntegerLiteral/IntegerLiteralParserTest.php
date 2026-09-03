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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Parser\IntegerLiteral;

use PackageFactory\ComponentEngine\Language\AST\Node\IntegerLiteral\IntegerFormat;
use PackageFactory\ComponentEngine\Language\AST\Node\IntegerLiteral\IntegerLiteralNode;
use PackageFactory\ComponentEngine\Language\Lexer\Lexer;
use PackageFactory\ComponentEngine\Language\Lexer\LexerException;
use PackageFactory\ComponentEngine\Language\Lexer\Rule\Rule;
use PackageFactory\ComponentEngine\Language\Parser\IntegerLiteral\IntegerLiteralCouldNotBeParsed;
use PackageFactory\ComponentEngine\Language\Parser\IntegerLiteral\IntegerLiteralParser;
use PackageFactory\ComponentEngine\Test\Unit\Language\Parser\ParserTestCase;

final class IntegerLiteralParserTest extends ParserTestCase
{
    /**
     * @test
     */
    public function parsesBinaryInteger(): void
    {
        $integerLiteralParser = IntegerLiteralParser::singleton();
        $lexer = new Lexer('0b1010110101');

        $expectedIntegerLiteralNode = new IntegerLiteralNode(
            rangeInSource: $this->range([0, 0], [0, 11]),
            format: IntegerFormat::BINARY,
            value: '0b1010110101'
        );

        $this->assertEquals(
            $expectedIntegerLiteralNode,
            $integerLiteralParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesNegativeBinaryInteger(): void
    {
        $integerLiteralParser = IntegerLiteralParser::singleton();
        $lexer = new Lexer('-0b1010110101');

        $expectedIntegerLiteralNode = new IntegerLiteralNode(
            rangeInSource: $this->range([0, 0], [0, 12]),
            format: IntegerFormat::BINARY,
            value: '-0b1010110101'
        );

        $this->assertEquals(
            $expectedIntegerLiteralNode,
            $integerLiteralParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesOctalInteger(): void
    {
        $integerLiteralParser = IntegerLiteralParser::singleton();
        $lexer = new Lexer('0o755');

        $expectedIntegerLiteralNode = new IntegerLiteralNode(
            rangeInSource: $this->range([0, 0], [0, 4]),
            format: IntegerFormat::OCTAL,
            value: '0o755'
        );

        $this->assertEquals(
            $expectedIntegerLiteralNode,
            $integerLiteralParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesNegativeOctalInteger(): void
    {
        $integerLiteralParser = IntegerLiteralParser::singleton();
        $lexer = new Lexer('-0o755');

        $expectedIntegerLiteralNode = new IntegerLiteralNode(
            rangeInSource: $this->range([0, 0], [0, 5]),
            format: IntegerFormat::OCTAL,
            value: '-0o755'
        );

        $this->assertEquals(
            $expectedIntegerLiteralNode,
            $integerLiteralParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesDecimalInteger(): void
    {
        $integerLiteralParser = IntegerLiteralParser::singleton();
        $lexer = new Lexer('1234567890');

        $expectedIntegerLiteralNode = new IntegerLiteralNode(
            rangeInSource: $this->range([0, 0], [0, 9]),
            format: IntegerFormat::DECIMAL,
            value: '1234567890'
        );

        $this->assertEquals(
            $expectedIntegerLiteralNode,
            $integerLiteralParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesNegativeDecimalInteger(): void
    {
        $integerLiteralParser = IntegerLiteralParser::singleton();
        $lexer = new Lexer('-1234567890');

        $expectedIntegerLiteralNode = new IntegerLiteralNode(
            rangeInSource: $this->range([0, 0], [0, 10]),
            format: IntegerFormat::DECIMAL,
            value: '-1234567890'
        );

        $this->assertEquals(
            $expectedIntegerLiteralNode,
            $integerLiteralParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesHexadecimalInteger(): void
    {
        $integerLiteralParser = IntegerLiteralParser::singleton();
        $lexer = new Lexer('0x123456789ABCDEF');

        $expectedIntegerLiteralNode = new IntegerLiteralNode(
            rangeInSource: $this->range([0, 0], [0, 16]),
            format: IntegerFormat::HEXADECIMAL,
            value: '0x123456789ABCDEF'
        );

        $this->assertEquals(
            $expectedIntegerLiteralNode,
            $integerLiteralParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesNegativeHexadecimalInteger(): void
    {
        $integerLiteralParser = IntegerLiteralParser::singleton();
        $lexer = new Lexer('-0x123456789ABCDEF');

        $expectedIntegerLiteralNode = new IntegerLiteralNode(
            rangeInSource: $this->range([0, 0], [0, 17]),
            format: IntegerFormat::HEXADECIMAL,
            value: '-0x123456789ABCDEF'
        );

        $this->assertEquals(
            $expectedIntegerLiteralNode,
            $integerLiteralParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function throwsIfTokenStreamEndsUnexpectedly(): void
    {
        $this->assertThrowsParserException(
            function () {
                $integerLiteralParser = IntegerLiteralParser::singleton();
                $lexer = new Lexer('');

                $integerLiteralParser->parse($lexer);
            },
            IntegerLiteralCouldNotBeParsed::becauseOfLexerException(
                cause: LexerException::becauseOfUnexpectedEndOfSource(
                    expectedRules: [
                        Rule::INTEGER_HEXADECIMAL,
                        Rule::INTEGER_DECIMAL,
                        Rule::INTEGER_OCTAL,
                        Rule::INTEGER_BINARY
                    ],
                    affectedRangeInSource: $this->range([0, 0], [0, 0])
                )
            )
        );
    }

    /**
     * @test
     */
    public function throwsIfUnexpectedTokenIsEncountered(): void
    {
        $this->assertThrowsParserException(
            function () {
                $integerLiteralParser = IntegerLiteralParser::singleton();
                $lexer = new Lexer('foo1234');

                $integerLiteralParser->parse($lexer);
            },
            IntegerLiteralCouldNotBeParsed::becauseOfLexerException(
                cause: LexerException::becauseOfUnexpectedCharacterSequence(
                    expectedRules: [
                        Rule::INTEGER_HEXADECIMAL,
                        Rule::INTEGER_DECIMAL,
                        Rule::INTEGER_OCTAL,
                        Rule::INTEGER_BINARY
                    ],
                    affectedRangeInSource: $this->range([0, 0], [0, 0]),
                    actualCharacterSequence: 'f'
                )
            )
        );
    }
}
