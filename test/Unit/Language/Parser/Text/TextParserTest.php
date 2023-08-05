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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Parser\Text;

use PackageFactory\ComponentEngine\Language\AST\Node\Text\TextNode;
use PackageFactory\ComponentEngine\Language\Parser\Text\TextParser;
use PackageFactory\ComponentEngine\Test\Unit\Language\Parser\ParserTestCase;

final class TextParserTest extends ParserTestCase
{
    /**
     * @test
     */
    public function parsesEmptyStringToNull(): void
    {
        $textParser = new TextParser();
        $tokens = $this->createTokenIterator('');

        $this->assertNull(
            $textParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesTextWithSpacesOnlyToNull(): void
    {
        $textParser = new TextParser();
        $tokens = $this->createTokenIterator(" \t \n \t ");

        $this->assertNull($textParser->parse($tokens));
    }

    /**
     * @test
     */
    public function parsesTrivialText(): void
    {
        $textParser = new TextParser();
        $tokens = $this->createTokenIterator('Hello World');

        $expectedTextNode = new TextNode(
            rangeInSource: $this->range([0, 0], [0, 10]),
            value: 'Hello World'
        );

        $this->assertEquals(
            $expectedTextNode,
            $textParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function trimsLeadingAndTrailingSpaces(): void
    {
        $textParser = new TextParser();
        $tokens = $this->createTokenIterator("  \t\t  Hello World  \t\t  ");

        $expectedTextNode = new TextNode(
            rangeInSource: $this->range([0, 0], [0, 22]),
            value: 'Hello World'
        );

        $this->assertEquals(
            $expectedTextNode,
            $textParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function trimsLeadingLineBreak(): void
    {
        $textParser = new TextParser();
        $tokens = $this->createTokenIterator("\nHello World");

        $expectedTextNode = new TextNode(
            rangeInSource: $this->range([0, 0], [1, 10]),
            value: 'Hello World'
        );

        $this->assertEquals(
            $expectedTextNode,
            $textParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function trimsLeadingLineBreakAndIndentation(): void
    {
        $textParser = new TextParser();
        $tokens = $this->createTokenIterator("\n    Hello World");

        $expectedTextNode = new TextNode(
            rangeInSource: $this->range([0, 0], [1, 14]),
            value: 'Hello World'
        );

        $this->assertEquals(
            $expectedTextNode,
            $textParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function preservesLeadingSpaceIfFlagIsSet(): void
    {
        $textParser = new TextParser();
        $tokens = $this->createTokenIterator("  \t\t  Hello World  \t\t  ");

        $expectedTextNode = new TextNode(
            rangeInSource: $this->range([0, 0], [0, 22]),
            value: ' Hello World'
        );

        $this->assertEquals(
            $expectedTextNode,
            $textParser->parse($tokens, true)
        );
    }

    /**
     * @test
     */
    public function reducesInnerSpacesToSingleSpaceCharacterEach(): void
    {
        $textParser = new TextParser();
        $tokens = $this->createTokenIterator("Hello \t \n \t folks   and\t\t\tpeople");

        $expectedTextNode = new TextNode(
            rangeInSource: $this->range([0, 0], [1, 22]),
            value: 'Hello folks and people'
        );

        $this->assertEquals(
            $expectedTextNode,
            $textParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function terminatesAtEmbeddedExpressionAndTrimsLeadingSpace(): void
    {
        $textParser = new TextParser();
        $tokens = $this->createTokenIterator("    Hello{");

        $expectedTextNode = new TextNode(
            rangeInSource: $this->range([0, 0], [0, 8]),
            value: 'Hello'
        );

        $this->assertEquals(
            $expectedTextNode,
            $textParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function terminatesAtEmbeddedExpressionAndKeepsTrailingSpace(): void
    {
        $textParser = new TextParser();
        $tokens = $this->createTokenIterator("Hello \t {foo}!");

        $expectedTextNode = new TextNode(
            rangeInSource: $this->range([0, 0], [0, 7]),
            value: 'Hello '
        );

        $this->assertEquals(
            $expectedTextNode,
            $textParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function terminatesAtEmbeddedExpressionAndTrimsTrailingSpaceIfItContainsLineBreaks(): void
    {
        $textParser = new TextParser();
        $tokens = $this->createTokenIterator("Hello \n\t {foo}!");

        $expectedTextNode = new TextNode(
            rangeInSource: $this->range([0, 0], [1, 1]),
            value: 'Hello'
        );

        $this->assertEquals(
            $expectedTextNode,
            $textParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function returnsNullAtEmbeddedExpressionIfTheresOnlySpace(): void
    {
        $textParser = new TextParser();
        $tokens = $this->createTokenIterator(" \n\t {foo}!");

        $this->assertNull($textParser->parse($tokens));
    }

    /**
     * @test
     */
    public function terminatesAtOpeningTagAndTrimsLeadingSpace(): void
    {
        $textParser = new TextParser();
        $tokens = $this->createTokenIterator("    Hello<a>");

        $expectedTextNode = new TextNode(
            rangeInSource: $this->range([0, 0], [0, 8]),
            value: 'Hello'
        );

        $this->assertEquals(
            $expectedTextNode,
            $textParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function terminatesAtOpeningTagAndKeepsTrailingSpace(): void
    {
        $textParser = new TextParser();
        $tokens = $this->createTokenIterator("Hello \t <a>World</a>");

        $expectedTextNode = new TextNode(
            rangeInSource: $this->range([0, 0], [0, 7]),
            value: 'Hello '
        );

        $this->assertEquals(
            $expectedTextNode,
            $textParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function terminatesAtOpeningTagAndTrimsTrailingSpaceIfItContainsLineBreaks(): void
    {
        $textParser = new TextParser();
        $tokens = $this->createTokenIterator("Hello \n\t <a>World</a>");

        $expectedTextNode = new TextNode(
            rangeInSource: $this->range([0, 0], [1, 1]),
            value: 'Hello'
        );

        $this->assertEquals(
            $expectedTextNode,
            $textParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function returnsNullAtOpeningTagIfTheresOnlySpace(): void
    {
        $textParser = new TextParser();
        $tokens = $this->createTokenIterator(" \n\t <a>");

        $this->assertNull($textParser->parse($tokens));
    }

    /**
     * @test
     */
    public function terminatesAtClosingTagAndTrimsTrailingSpace(): void
    {
        $textParser = new TextParser();
        $tokens = $this->createTokenIterator("World \n\t </a>");

        $expectedTextNode = new TextNode(
            rangeInSource: $this->range([0, 0], [1, 1]),
            value: 'World'
        );

        $this->assertEquals(
            $expectedTextNode,
            $textParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function returnsNullAtClosingTagIfTheresOnlySpace(): void
    {
        $textParser = new TextParser();
        $tokens = $this->createTokenIterator(" \n\t </a>");

        $this->assertNull($textParser->parse($tokens));
    }
}
