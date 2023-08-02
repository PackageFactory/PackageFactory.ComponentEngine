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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Parser\Tag;

use PackageFactory\ComponentEngine\Domain\AttributeName\AttributeName;
use PackageFactory\ComponentEngine\Domain\TagName\TagName;
use PackageFactory\ComponentEngine\Language\AST\Node\StringLiteral\StringLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\AttributeNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\AttributeNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\AttributeNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\ChildNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\TagNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\TagNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Text\TextNode;
use PackageFactory\ComponentEngine\Language\Parser\Tag\TagParser;
use PackageFactory\ComponentEngine\Language\AST\NodeAttributes\NodeAttributes;
use PackageFactory\ComponentEngine\Language\Parser\ParserException;
use PackageFactory\ComponentEngine\Language\Parser\Tag\TagCouldNotBeParsed;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Tokenizer;
use PHPUnit\Framework\TestCase;

final class TagParserTest extends TestCase
{
    /**
     * @test
     */
    public function parsesSelfClosingTagWithoutAttributes(): void
    {
        $tagParser = new TagParser();
        $tokens = Tokenizer::fromSource(Source::fromString('<a/>'))->getIterator();

        $expectedTagNode = new TagNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 3)
                )
            ),
            name: new TagNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 1),
                        new Position(0, 1)
                    )
                ),
                value: TagName::from('a')
            ),
            tagAttributes: new AttributeNodes(),
            children: new ChildNodes(),
            isSelfClosing: true
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesSelfClosingTagWithValuelessAttribute(): void
    {
        $tagParser = new TagParser();
        $tokens = Tokenizer::fromSource(Source::fromString('<table foo/>'))->getIterator();

        $expectedTagNode = new TagNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 11)
                )
            ),
            name: new TagNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 1),
                        new Position(0, 5)
                    )
                ),
                value: TagName::from('table')
            ),
            tagAttributes: new AttributeNodes(
                new AttributeNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 7),
                            new Position(0, 9)
                        )
                    ),
                    name: new AttributeNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 7),
                                new Position(0, 9)
                            )
                        ),
                        value: AttributeName::from('foo')
                    ),
                    value: null
                )
            ),
            children: new ChildNodes(),
            isSelfClosing: true
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesSelfClosingTagWithMultipleValuelessAttributes(): void
    {
        $tagParser = new TagParser();
        $tokens = Tokenizer::fromSource(Source::fromString('<table foo bar baz/>'))->getIterator();

        $expectedTagNode = new TagNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 19)
                )
            ),
            name: new TagNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 1),
                        new Position(0, 5)
                    )
                ),
                value: TagName::from('table')
            ),
            tagAttributes: new AttributeNodes(
                new AttributeNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 7),
                            new Position(0, 9)
                        )
                    ),
                    name: new AttributeNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 7),
                                new Position(0, 9)
                            )
                        ),
                        value: AttributeName::from('foo')
                    ),
                    value: null
                ),
                new AttributeNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 11),
                            new Position(0, 13)
                        )
                    ),
                    name: new AttributeNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 11),
                                new Position(0, 13)
                            )
                        ),
                        value: AttributeName::from('bar')
                    ),
                    value: null
                ),
                new AttributeNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 15),
                            new Position(0, 17)
                        )
                    ),
                    name: new AttributeNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 15),
                                new Position(0, 17)
                            )
                        ),
                        value: AttributeName::from('baz')
                    ),
                    value: null
                )
            ),
            children: new ChildNodes(),
            isSelfClosing: true
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesSelfClosingTagWithStringAttribute(): void
    {
        $tagParser = new TagParser();
        $tokens = Tokenizer::fromSource(Source::fromString('<a foo="bar"/>'))->getIterator();

        $expectedTagNode = new TagNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 13)
                )
            ),
            name: new TagNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 1),
                        new Position(0, 1)
                    )
                ),
                value: TagName::from('a')
            ),
            tagAttributes: new AttributeNodes(
                new AttributeNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 3),
                            new Position(0, 10)
                        )
                    ),
                    name: new AttributeNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 3),
                                new Position(0, 5)
                            )
                        ),
                        value: AttributeName::from('foo')
                    ),
                    value: new StringLiteralNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 8),
                                new Position(0, 10)
                            )
                        ),
                        value: 'bar'
                    )
                )
            ),
            children: new ChildNodes(),
            isSelfClosing: true
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesSelfClosingTagWithMultipleStringAttributes(): void
    {
        $tagParser = new TagParser();
        $tokens = Tokenizer::fromSource(Source::fromString('<div foo="bar" baz="qux" quux="corge"/>'))->getIterator();

        $expectedTagNode = new TagNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 38)
                )
            ),
            name: new TagNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 1),
                        new Position(0, 3)
                    )
                ),
                value: TagName::from('div')
            ),
            tagAttributes: new AttributeNodes(
                new AttributeNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 5),
                            new Position(0, 12)
                        )
                    ),
                    name: new AttributeNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 5),
                                new Position(0, 7)
                            )
                        ),
                        value: AttributeName::from('foo')
                    ),
                    value: new StringLiteralNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 10),
                                new Position(0, 12)
                            )
                        ),
                        value: 'bar'
                    )
                ),
                new AttributeNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 15),
                            new Position(0, 22)
                        )
                    ),
                    name: new AttributeNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 15),
                                new Position(0, 17)
                            )
                        ),
                        value: AttributeName::from('baz')
                    ),
                    value: new StringLiteralNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 20),
                                new Position(0, 22)
                            )
                        ),
                        value: 'qux'
                    )
                ),
                new AttributeNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 25),
                            new Position(0, 35)
                        )
                    ),
                    name: new AttributeNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 25),
                                new Position(0, 28)
                            )
                        ),
                        value: AttributeName::from('quux')
                    ),
                    value: new StringLiteralNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 31),
                                new Position(0, 35)
                            )
                        ),
                        value: 'corge'
                    )
                )
            ),
            children: new ChildNodes(),
            isSelfClosing: true
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithEmptyContentAndWithoutAttributes(): void
    {
        $tagParser = new TagParser();
        $tokens = Tokenizer::fromSource(Source::fromString('<a></a>'))->getIterator();

        $expectedTagNode = new TagNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 6)
                )
            ),
            name: new TagNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 1),
                        new Position(0, 1)
                    )
                ),
                value: TagName::from('a')
            ),
            tagAttributes: new AttributeNodes(),
            children: new ChildNodes(),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function throwsIfClosingTagNameDoesNotMatchOpeningTagName(): void
    {
        $tagParser = new TagParser();
        $tokens = Tokenizer::fromSource(Source::fromString('<a></b>'))->getIterator();

        $this->expectException(ParserException::class);
        $this->expectExceptionObject(
            TagCouldNotBeParsed::becauseOfClosingTagNameMismatch(
                expectedTagName: TagName::from('a'),
                actualTagName: 'b',
                affectedRangeInSource: Range::from(
                    new Position(0, 5),
                    new Position(0, 5)
                )
            )
        );

        $tagParser->parse($tokens);
    }

    /**
     * @test
     */
    public function parsesTagWithEmptyContentAndValuelessAttribute(): void
    {
        $tagParser = new TagParser();
        $tokens = Tokenizer::fromSource(Source::fromString('<a foo></a>'))->getIterator();

        $expectedTagNode = new TagNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 10)
                )
            ),
            name: new TagNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 1),
                        new Position(0, 1)
                    )
                ),
                value: TagName::from('a')
            ),
            tagAttributes: new AttributeNodes(
                new AttributeNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 3),
                            new Position(0, 5)
                        )
                    ),
                    name: new AttributeNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 3),
                                new Position(0, 5)
                            )
                        ),
                        value: AttributeName::from('foo')
                    ),
                    value: null
                ),
            ),
            children: new ChildNodes(),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithEmptyContentAndMultipleValuelessAttributes(): void
    {
        $tagParser = new TagParser();
        $tokens = Tokenizer::fromSource(Source::fromString('<a foo bar baz></a>'))->getIterator();

        $expectedTagNode = new TagNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 18)
                )
            ),
            name: new TagNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 1),
                        new Position(0, 1)
                    )
                ),
                value: TagName::from('a')
            ),
            tagAttributes: new AttributeNodes(
                new AttributeNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 3),
                            new Position(0, 5)
                        )
                    ),
                    name: new AttributeNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 3),
                                new Position(0, 5)
                            )
                        ),
                        value: AttributeName::from('foo')
                    ),
                    value: null
                ),
                new AttributeNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 7),
                            new Position(0, 9)
                        )
                    ),
                    name: new AttributeNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 7),
                                new Position(0, 9)
                            )
                        ),
                        value: AttributeName::from('bar')
                    ),
                    value: null
                ),
                new AttributeNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 11),
                            new Position(0, 13)
                        )
                    ),
                    name: new AttributeNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 11),
                                new Position(0, 13)
                            )
                        ),
                        value: AttributeName::from('baz')
                    ),
                    value: null
                ),
            ),
            children: new ChildNodes(),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithEmptyContentAndStringAttribute(): void
    {
        $tagParser = new TagParser();
        $tokens = Tokenizer::fromSource(Source::fromString('<audio foo="bar"></audio>'))->getIterator();

        $expectedTagNode = new TagNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 24)
                )
            ),
            name: new TagNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 1),
                        new Position(0, 5)
                    )
                ),
                value: TagName::from('audio')
            ),
            tagAttributes: new AttributeNodes(
                new AttributeNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 7),
                            new Position(0, 14)
                        )
                    ),
                    name: new AttributeNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 7),
                                new Position(0, 9)
                            )
                        ),
                        value: AttributeName::from('foo')
                    ),
                    value: new StringLiteralNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 12),
                                new Position(0, 14)
                            )
                        ),
                        value: 'bar'
                    )
                ),
            ),
            children: new ChildNodes(),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithEmptyContentAndMultipleStringAttributes(): void
    {
        $tagParser = new TagParser();
        $tokens = Tokenizer::fromSource(Source::fromString('<video foo="bar" baz="qux" quux="corge"></video>'))->getIterator();

        $expectedTagNode = new TagNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 47)
                )
            ),
            name: new TagNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 1),
                        new Position(0, 5)
                    )
                ),
                value: TagName::from('video')
            ),
            tagAttributes: new AttributeNodes(
                new AttributeNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 7),
                            new Position(0, 14)
                        )
                    ),
                    name: new AttributeNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 7),
                                new Position(0, 9)
                            )
                        ),
                        value: AttributeName::from('foo')
                    ),
                    value: new StringLiteralNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 12),
                                new Position(0, 14)
                            )
                        ),
                        value: 'bar'
                    )
                ),
                new AttributeNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 17),
                            new Position(0, 24)
                        )
                    ),
                    name: new AttributeNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 17),
                                new Position(0, 19)
                            )
                        ),
                        value: AttributeName::from('baz')
                    ),
                    value: new StringLiteralNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 22),
                                new Position(0, 24)
                            )
                        ),
                        value: 'qux'
                    )
                ),
                new AttributeNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 27),
                            new Position(0, 37)
                        )
                    ),
                    name: new AttributeNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 27),
                                new Position(0, 30)
                            )
                        ),
                        value: AttributeName::from('quux')
                    ),
                    value: new StringLiteralNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 33),
                                new Position(0, 37)
                            )
                        ),
                        value: 'corge'
                    )
                ),
            ),
            children: new ChildNodes(),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithTextContentAndWithoutAttributes(): void
    {
        $tagParser = new TagParser();
        $tokens = Tokenizer::fromSource(Source::fromString('<a>Lorem ipsum...</a>'))->getIterator();

        $expectedTagNode = new TagNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 20)
                )
            ),
            name: new TagNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 1),
                        new Position(0, 1)
                    )
                ),
                value: TagName::from('a')
            ),
            tagAttributes: new AttributeNodes(),
            children: new ChildNodes(
                new TextNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 3),
                            new Position(0, 16)
                        )
                    ),
                    value: 'Lorem ipsum...'
                )
            ),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithNestedSelfClosingTagContentAndWithoutAttributes(): void
    {
        $tagParser = new TagParser();
        $tokens = Tokenizer::fromSource(Source::fromString('<a><b/></a>'))->getIterator();

        $expectedTagNode = new TagNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 10)
                )
            ),
            name: new TagNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 1),
                        new Position(0, 1)
                    )
                ),
                value: TagName::from('a')
            ),
            tagAttributes: new AttributeNodes(),
            children: new ChildNodes(
                new TagNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 3),
                            new Position(0, 6)
                        )
                    ),
                    name: new TagNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 4),
                                new Position(0, 4)
                            )
                        ),
                        value: TagName::from('b')
                    ),
                    tagAttributes: new AttributeNodes(),
                    children: new ChildNodes(),
                    isSelfClosing: true
                )
            ),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithNestedTagAndWithoutAttributes(): void
    {
        $tagParser = new TagParser();
        $tokens = Tokenizer::fromSource(Source::fromString('<a><b></b></a>'))->getIterator();

        $expectedTagNode = new TagNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 13)
                )
            ),
            name: new TagNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 1),
                        new Position(0, 1)
                    )
                ),
                value: TagName::from('a')
            ),
            tagAttributes: new AttributeNodes(),
            children: new ChildNodes(
                new TagNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 3),
                            new Position(0, 9)
                        )
                    ),
                    name: new TagNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 4),
                                new Position(0, 4)
                            )
                        ),
                        value: TagName::from('b')
                    ),
                    tagAttributes: new AttributeNodes(),
                    children: new ChildNodes(),
                    isSelfClosing: false
                )
            ),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithNestedTagsOnMultipleLevelsAndWithoutAttributes(): void
    {
        $tagParser = new TagParser();
        $tokens = Tokenizer::fromSource(Source::fromString('<a><b><c><d/></c></b></a>'))->getIterator();

        $expectedTagNode = new TagNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 24)
                )
            ),
            name: new TagNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 1),
                        new Position(0, 1)
                    )
                ),
                value: TagName::from('a')
            ),
            tagAttributes: new AttributeNodes(),
            children: new ChildNodes(
                new TagNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 3),
                            new Position(0, 20)
                        )
                    ),
                    name: new TagNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 4),
                                new Position(0, 4)
                            )
                        ),
                        value: TagName::from('b')
                    ),
                    tagAttributes: new AttributeNodes(),
                    children: new ChildNodes(
                        new TagNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(0, 6),
                                    new Position(0, 16)
                                )
                            ),
                            name: new TagNameNode(
                                attributes: new NodeAttributes(
                                    rangeInSource: Range::from(
                                        new Position(0, 7),
                                        new Position(0, 7)
                                    )
                                ),
                                value: TagName::from('c')
                            ),
                            tagAttributes: new AttributeNodes(),
                            children: new ChildNodes(
                                new TagNode(
                                    attributes: new NodeAttributes(
                                        rangeInSource: Range::from(
                                            new Position(0, 9),
                                            new Position(0, 12)
                                        )
                                    ),
                                    name: new TagNameNode(
                                        attributes: new NodeAttributes(
                                            rangeInSource: Range::from(
                                                new Position(0, 10),
                                                new Position(0, 10)
                                            )
                                        ),
                                        value: TagName::from('d')
                                    ),
                                    tagAttributes: new AttributeNodes(),
                                    children: new ChildNodes(),
                                    isSelfClosing: true
                                )
                            ),
                            isSelfClosing: false
                        )
                    ),
                    isSelfClosing: false
                )
            ),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithNestedTagInBetweenSpacesAndWithoutAttributes(): void
    {
        $tagParser = new TagParser();
        $tokens = Tokenizer::fromSource(Source::fromString('<a>   <b></b>   </a>'))->getIterator();

        $expectedTagNode = new TagNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 19)
                )
            ),
            name: new TagNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 1),
                        new Position(0, 1)
                    )
                ),
                value: TagName::from('a')
            ),
            tagAttributes: new AttributeNodes(),
            children: new ChildNodes(
                new TagNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 6),
                            new Position(0, 12)
                        )
                    ),
                    name: new TagNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 7),
                                new Position(0, 7)
                            )
                        ),
                        value: TagName::from('b')
                    ),
                    tagAttributes: new AttributeNodes(),
                    children: new ChildNodes(),
                    isSelfClosing: false
                )
            ),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithNestedTagInBetweenTextContentPreservingSpaceAroundTheNestedTag(): void
    {
        $tagParser = new TagParser();
        $tokens = Tokenizer::fromSource(Source::fromString('<a>Something <b>important</b> happened.</a>'))->getIterator();

        $expectedTagNode = new TagNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 42)
                )
            ),
            name: new TagNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 1),
                        new Position(0, 1)
                    )
                ),
                value: TagName::from('a')
            ),
            tagAttributes: new AttributeNodes(),
            children: new ChildNodes(
                new TextNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 3),
                            new Position(0, 12)
                        )
                    ),
                    value: 'Something '
                ),
                new TagNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 13),
                            new Position(0, 28)
                        )
                    ),
                    name: new TagNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 14),
                                new Position(0, 14)
                            )
                        ),
                        value: TagName::from('b')
                    ),
                    tagAttributes: new AttributeNodes(),
                    children: new ChildNodes(
                        new TextNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(0, 16),
                                    new Position(0, 24)
                                )
                            ),
                            value: 'important'
                        )
                    ),
                    isSelfClosing: false
                ),
                new TextNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 29),
                            new Position(0, 38)
                        )
                    ),
                    value: ' happened.'
                )
            ),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithMultipleNestedTagsAsImmediateChildren(): void
    {
        $tagParser = new TagParser();
        $tokens = Tokenizer::fromSource(Source::fromString('<a><b></b><c/><d></d></a>'))->getIterator();

        $expectedTagNode = new TagNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 24)
                )
            ),
            name: new TagNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 1),
                        new Position(0, 1)
                    )
                ),
                value: TagName::from('a')
            ),
            tagAttributes: new AttributeNodes(),
            children: new ChildNodes(
                new TagNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 3),
                            new Position(0, 9)
                        )
                    ),
                    name: new TagNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 4),
                                new Position(0, 4)
                            )
                        ),
                        value: TagName::from('b')
                    ),
                    tagAttributes: new AttributeNodes(),
                    children: new ChildNodes(),
                    isSelfClosing: false
                ),
                new TagNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 10),
                            new Position(0, 13)
                        )
                    ),
                    name: new TagNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 11),
                                new Position(0, 11)
                            )
                        ),
                        value: TagName::from('c')
                    ),
                    tagAttributes: new AttributeNodes(),
                    children: new ChildNodes(),
                    isSelfClosing: true
                ),
                new TagNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 14),
                            new Position(0, 20)
                        )
                    ),
                    name: new TagNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 15),
                                new Position(0, 15)
                            )
                        ),
                        value: TagName::from('d')
                    ),
                    tagAttributes: new AttributeNodes(),
                    children: new ChildNodes(),
                    isSelfClosing: false
                ),
            ),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithMultipleNestedTagsOnMultipleLevelsAllHavingAttributesAndContentsThemselves(): void
    {
        $tagParser = new TagParser();
        $tagAsString = <<<AFX
        <div class="test" hidden>
            Some opening text
            <h1>Headline</h1>
            <a href="about:blank" target="_blank">This is a link</a>
            <p class="rte">
                This is a paragraph with <em>emphasized</em> and <strong>boldened</strong> text.
            </p>
            Some closing text
        </div>
        AFX;
        $tokens = Tokenizer::fromSource(Source::fromString($tagAsString))->getIterator();

        $expectedTagNode = new TagNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(8, 5)
                )
            ),
            name: new TagNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 1),
                        new Position(0, 3)
                    )
                ),
                value: TagName::from('div')
            ),
            tagAttributes: new AttributeNodes(
                new AttributeNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 5),
                            new Position(0, 15)
                        )
                    ),
                    name: new AttributeNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 5),
                                new Position(0, 9)
                            )
                        ),
                        value: AttributeName::from('class')
                    ),
                    value: new StringLiteralNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 12),
                                new Position(0, 15)
                            )
                        ),
                        value: 'test'
                    )
                ),
                new AttributeNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 18),
                            new Position(0, 23)
                        )
                    ),
                    name: new AttributeNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 18),
                                new Position(0, 23)
                            )
                        ),
                        value: AttributeName::from('hidden')
                    ),
                    value: null
                ),
            ),
            children: new ChildNodes(
                new TextNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 25),
                            new Position(2, 3)
                        )
                    ),
                    value: 'Some opening text'
                ),
                new TagNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(2, 4),
                            new Position(2, 20)
                        )
                    ),
                    name: new TagNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(2, 5),
                                new Position(2, 6)
                            )
                        ),
                        value: TagName::from('h1')
                    ),
                    tagAttributes: new AttributeNodes(),
                    children: new ChildNodes(
                        new TextNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(2, 8),
                                    new Position(2, 15)
                                )
                            ),
                            value: 'Headline'
                        ),
                    ),
                    isSelfClosing: false
                ),
                new TagNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(3, 4),
                            new Position(3, 59)
                        )
                    ),
                    name: new TagNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(3, 5),
                                new Position(3, 5)
                            )
                        ),
                        value: TagName::from('a')
                    ),
                    tagAttributes: new AttributeNodes(
                        new AttributeNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(3, 7),
                                    new Position(3, 23)
                                )
                            ),
                            name: new AttributeNameNode(
                                attributes: new NodeAttributes(
                                    rangeInSource: Range::from(
                                        new Position(3, 7),
                                        new Position(3, 10)
                                    )
                                ),
                                value: AttributeName::from('href')
                            ),
                            value: new StringLiteralNode(
                                attributes: new NodeAttributes(
                                    rangeInSource: Range::from(
                                        new Position(3, 13),
                                        new Position(3, 23)
                                    )
                                ),
                                value: 'about:blank'
                            )
                        ),
                        new AttributeNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(3, 26),
                                    new Position(3, 39)
                                )
                            ),
                            name: new AttributeNameNode(
                                attributes: new NodeAttributes(
                                    rangeInSource: Range::from(
                                        new Position(3, 26),
                                        new Position(3, 31)
                                    )
                                ),
                                value: AttributeName::from('target')
                            ),
                            value: new StringLiteralNode(
                                attributes: new NodeAttributes(
                                    rangeInSource: Range::from(
                                        new Position(3, 34),
                                        new Position(3, 39)
                                    )
                                ),
                                value: '_blank'
                            )
                        ),
                    ),
                    children: new ChildNodes(
                        new TextNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(3, 42),
                                    new Position(3, 55)
                                )
                            ),
                            value: 'This is a link'
                        ),
                    ),
                    isSelfClosing: false
                ),
                new TagNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(4, 4),
                            new Position(6, 7)
                        )
                    ),
                    name: new TagNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(4, 5),
                                new Position(4, 5)
                            )
                        ),
                        value: TagName::from('p')
                    ),
                    tagAttributes: new AttributeNodes(
                        new AttributeNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(4, 7),
                                    new Position(4, 16)
                                )
                            ),
                            name: new AttributeNameNode(
                                attributes: new NodeAttributes(
                                    rangeInSource: Range::from(
                                        new Position(4, 7),
                                        new Position(4, 11)
                                    )
                                ),
                                value: AttributeName::from('class')
                            ),
                            value: new StringLiteralNode(
                                attributes: new NodeAttributes(
                                    rangeInSource: Range::from(
                                        new Position(4, 14),
                                        new Position(4, 16)
                                    )
                                ),
                                value: 'rte'
                            )
                        ),
                    ),
                    children: new ChildNodes(
                        new TextNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(4, 19),
                                    new Position(5, 32)
                                )
                            ),
                            value: 'This is a paragraph with '
                        ),
                        new TagNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(5, 33),
                                    new Position(5, 51)
                                )
                            ),
                            name: new TagNameNode(
                                attributes: new NodeAttributes(
                                    rangeInSource: Range::from(
                                        new Position(5, 34),
                                        new Position(5, 35)
                                    )
                                ),
                                value: TagName::from('em')
                            ),
                            tagAttributes: new AttributeNodes(),
                            children: new ChildNodes(
                                new TextNode(
                                    attributes: new NodeAttributes(
                                        rangeInSource: Range::from(
                                            new Position(5, 37),
                                            new Position(5, 46)
                                        )
                                    ),
                                    value: 'emphasized'
                                ),
                            ),
                            isSelfClosing: false
                        ),
                        new TextNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(5, 52),
                                    new Position(5, 56)
                                )
                            ),
                            value: ' and '
                        ),
                        new TagNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(5, 57),
                                    new Position(5, 81)
                                )
                            ),
                            name: new TagNameNode(
                                attributes: new NodeAttributes(
                                    rangeInSource: Range::from(
                                        new Position(5, 58),
                                        new Position(5, 63)
                                    )
                                ),
                                value: TagName::from('strong')
                            ),
                            tagAttributes: new AttributeNodes(),
                            children: new ChildNodes(
                                new TextNode(
                                    attributes: new NodeAttributes(
                                        rangeInSource: Range::from(
                                            new Position(5, 65),
                                            new Position(5, 72)
                                        )
                                    ),
                                    value: 'boldened'
                                ),
                            ),
                            isSelfClosing: false
                        ),
                        new TextNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(5, 82),
                                    new Position(6, 3)
                                )
                            ),
                            value: ' text.'
                        )
                    ),
                    isSelfClosing: false
                ),
                new TextNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(6, 8),
                            new Position(7, 21)
                        )
                    ),
                    value: 'Some closing text'
                ),
            ),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($tokens)
        );
    }
}
