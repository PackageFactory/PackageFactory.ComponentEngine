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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Parser\Text;

use PackageFactory\ComponentEngine\Language\AST\Node\Text\TextNode;
use PackageFactory\ComponentEngine\Language\AST\NodeAttributes\NodeAttributes;
use PackageFactory\ComponentEngine\Language\Parser\Text\TextParser;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Tokenizer;
use PHPUnit\Framework\TestCase;

final class TextParserTest extends TestCase
{
    /**
     * @test
     */
    public function parsesEmptyStringToNull(): void
    {
        $textParser = new TextParser();
        $tokens = Tokenizer::fromSource(Source::fromString(''))->getIterator();

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
        $tokens = Tokenizer::fromSource(Source::fromString(" \t \n \t "))->getIterator();

        $this->assertNull($textParser->parse($tokens));
    }

    /**
     * @test
     */
    public function parsesTrivialText(): void
    {
        $textParser = new TextParser();
        $tokens = Tokenizer::fromSource(Source::fromString('Hello World'))->getIterator();

        $expectedTextNode = new TextNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 10)
                )
            ),
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
        $tokens = Tokenizer::fromSource(Source::fromString("  \t\t  Hello World  \t\t  "))->getIterator();

        $expectedTextNode = new TextNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 22)
                )
            ),
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
        $tokens = Tokenizer::fromSource(Source::fromString("\nHello World"))->getIterator();

        $expectedTextNode = new TextNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(1, 10)
                )
            ),
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
        $tokens = Tokenizer::fromSource(Source::fromString("\n    Hello World"))->getIterator();

        $expectedTextNode = new TextNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(1, 14)
                )
            ),
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
        $tokens = Tokenizer::fromSource(Source::fromString("  \t\t  Hello World  \t\t  "))->getIterator();

        $expectedTextNode = new TextNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 22)
                )
            ),
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
        $tokens = Tokenizer::fromSource(Source::fromString("Hello \t \n \t folks   and\t\t\tpeople"))->getIterator();

        $expectedTextNode = new TextNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(1, 22)
                )
            ),
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
        $tokens = Tokenizer::fromSource(Source::fromString("    Hello{"))->getIterator();

        $expectedTextNode = new TextNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 8)
                )
            ),
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
        $tokens = Tokenizer::fromSource(Source::fromString("Hello \t {foo}!"))->getIterator();

        $expectedTextNode = new TextNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 7)
                )
            ),
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
        $tokens = Tokenizer::fromSource(Source::fromString("Hello \n\t {foo}!"))->getIterator();

        $expectedTextNode = new TextNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(1, 1)
                )
            ),
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
        $tokens = Tokenizer::fromSource(Source::fromString(" \n\t {foo}!"))->getIterator();

        $this->assertNull($textParser->parse($tokens));
    }

    /**
     * @test
     */
    public function terminatesAtOpeningTagAndTrimsLeadingSpace(): void
    {
        $textParser = new TextParser();
        $tokens = Tokenizer::fromSource(Source::fromString("    Hello<a>"))->getIterator();

        $expectedTextNode = new TextNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 8)
                )
            ),
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
        $tokens = Tokenizer::fromSource(Source::fromString("Hello \t <a>World</a>"))->getIterator();

        $expectedTextNode = new TextNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 7)
                )
            ),
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
        $tokens = Tokenizer::fromSource(Source::fromString("Hello \n\t <a>World</a>"))->getIterator();

        $expectedTextNode = new TextNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(1, 1)
                )
            ),
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
        $tokens = Tokenizer::fromSource(Source::fromString(" \n\t <a>"))->getIterator();

        $this->assertNull($textParser->parse($tokens));
    }

    /**
     * @test
     */
    public function terminatesAtClosingTagAndTrimsTrailingSpace(): void
    {
        $textParser = new TextParser();
        $tokens = Tokenizer::fromSource(Source::fromString("World \n\t </a>"))->getIterator();

        $expectedTextNode = new TextNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(1, 1)
                )
            ),
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
        $tokens = Tokenizer::fromSource(Source::fromString(" \n\t </a>"))->getIterator();

        $this->assertNull($textParser->parse($tokens));
    }
}
