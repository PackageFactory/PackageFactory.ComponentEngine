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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Parser\Tag;

use PackageFactory\ComponentEngine\Domain\AttributeName\AttributeName;
use PackageFactory\ComponentEngine\Domain\TagName\TagName;
use PackageFactory\ComponentEngine\Domain\VariableName\VariableName;
use PackageFactory\ComponentEngine\Language\AST\Node\Expression\ExpressionNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StringLiteral\StringLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\AttributeNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\AttributeNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\AttributeNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\ChildNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\TagNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\TagNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Text\TextNode;
use PackageFactory\ComponentEngine\Language\AST\Node\ValueReference\ValueReferenceNode;
use PackageFactory\ComponentEngine\Language\Lexer\Lexer;
use PackageFactory\ComponentEngine\Language\Parser\Tag\TagParser;
use PackageFactory\ComponentEngine\Language\Parser\Tag\TagCouldNotBeParsed;
use PackageFactory\ComponentEngine\Test\Unit\Language\Parser\ParserTestCase;

final class TagParserTest extends ParserTestCase
{
    /**
     * @test
     */
    public function parsesSelfClosingTagWithoutAttributes(): void
    {
        $tagParser = TagParser::singleton();
        $lexer = new Lexer('<a/>');

        $expectedTagNode = new TagNode(
            rangeInSource: $this->range([0, 0], [0, 3]),
            name: new TagNameNode(
                rangeInSource: $this->range([0, 1], [0, 1]),
                value: TagName::from('a')
            ),
            attributes: new AttributeNodes(),
            children: new ChildNodes(),
            isSelfClosing: true
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesSelfClosingTagWithValuelessAttribute(): void
    {
        $tagParser = TagParser::singleton();
        $lexer = new Lexer('<table foo/>');

        $expectedTagNode = new TagNode(
            rangeInSource: $this->range([0, 0], [0, 11]),
            name: new TagNameNode(
                rangeInSource: $this->range([0, 1], [0, 5]),
                value: TagName::from('table')
            ),
            attributes: new AttributeNodes(
                new AttributeNode(
                    rangeInSource: $this->range([0, 7], [0, 9]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 7], [0, 9]),
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
            $tagParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesSelfClosingTagWithMultipleValuelessAttributes(): void
    {
        $tagParser = TagParser::singleton();
        $lexer = new Lexer('<table foo bar baz/>');

        $expectedTagNode = new TagNode(
            rangeInSource: $this->range([0, 0], [0, 19]),
            name: new TagNameNode(
                rangeInSource: $this->range([0, 1], [0, 5]),
                value: TagName::from('table')
            ),
            attributes: new AttributeNodes(
                new AttributeNode(
                    rangeInSource: $this->range([0, 7], [0, 9]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 7], [0, 9]),
                        value: AttributeName::from('foo')
                    ),
                    value: null
                ),
                new AttributeNode(
                    rangeInSource: $this->range([0, 11], [0, 13]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 11], [0, 13]),
                        value: AttributeName::from('bar')
                    ),
                    value: null
                ),
                new AttributeNode(
                    rangeInSource: $this->range([0, 15], [0, 17]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 15], [0, 17]),
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
            $tagParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesSelfClosingTagWithStringAttribute(): void
    {
        $tagParser = TagParser::singleton();
        $lexer = new Lexer('<a foo="bar"/>');

        $expectedTagNode = new TagNode(
            rangeInSource: $this->range([0, 0], [0, 13]),
            name: new TagNameNode(
                rangeInSource: $this->range([0, 1], [0, 1]),
                value: TagName::from('a')
            ),
            attributes: new AttributeNodes(
                new AttributeNode(
                    rangeInSource: $this->range([0, 3], [0, 11]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 3], [0, 5]),
                        value: AttributeName::from('foo')
                    ),
                    value: new StringLiteralNode(
                        rangeInSource: $this->range([0, 7], [0, 11]),
                        value: 'bar'
                    )
                )
            ),
            children: new ChildNodes(),
            isSelfClosing: true
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesSelfClosingTagWithMultipleStringAttributes(): void
    {
        $tagParser = TagParser::singleton();
        $lexer = new Lexer('<div foo="bar" baz="qux" quux="corge"/>');

        $expectedTagNode = new TagNode(
            rangeInSource: $this->range([0, 0], [0, 38]),
            name: new TagNameNode(
                rangeInSource: $this->range([0, 1], [0, 3]),
                value: TagName::from('div')
            ),
            attributes: new AttributeNodes(
                new AttributeNode(
                    rangeInSource: $this->range([0, 5], [0, 13]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 5], [0, 7]),
                        value: AttributeName::from('foo')
                    ),
                    value: new StringLiteralNode(
                        rangeInSource: $this->range([0, 9], [0, 13]),
                        value: 'bar'
                    )
                ),
                new AttributeNode(
                    rangeInSource: $this->range([0, 15], [0, 23]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 15], [0, 17]),
                        value: AttributeName::from('baz')
                    ),
                    value: new StringLiteralNode(
                        rangeInSource: $this->range([0, 19], [0, 23]),
                        value: 'qux'
                    )
                ),
                new AttributeNode(
                    rangeInSource: $this->range([0, 25], [0, 36]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 25], [0, 28]),
                        value: AttributeName::from('quux')
                    ),
                    value: new StringLiteralNode(
                        rangeInSource: $this->range([0, 30], [0, 36]),
                        value: 'corge'
                    )
                )
            ),
            children: new ChildNodes(),
            isSelfClosing: true
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesSelfClosingTagWithExpressionAttribute(): void
    {
        $tagParser = TagParser::singleton();
        $lexer = new Lexer('<a foo={bar}/>');

        $expectedTagNode = new TagNode(
            rangeInSource: $this->range([0, 0], [0, 13]),
            name: new TagNameNode(
                rangeInSource: $this->range([0, 1], [0, 1]),
                value: TagName::from('a')
            ),
            attributes: new AttributeNodes(
                new AttributeNode(
                    rangeInSource: $this->range([0, 3], [0, 10]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 3], [0, 5]),
                        value: AttributeName::from('foo')
                    ),
                    value: new ExpressionNode(
                        rangeInSource: $this->range([0, 8], [0, 10]),
                        root: new ValueReferenceNode(
                            rangeInSource: $this->range([0, 8], [0, 10]),
                            name: VariableName::from('bar')
                        )
                    )
                )
            ),
            children: new ChildNodes(),
            isSelfClosing: true
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesSelfClosingTagWithMultipleExpressionAttributes(): void
    {
        $tagParser = TagParser::singleton();
        $lexer = new Lexer('<div foo={bar} baz={qux} quux={corge}/>');

        $expectedTagNode = new TagNode(
            rangeInSource: $this->range([0, 0], [0, 38]),
            name: new TagNameNode(
                rangeInSource: $this->range([0, 1], [0, 3]),
                value: TagName::from('div')
            ),
            attributes: new AttributeNodes(
                new AttributeNode(
                    rangeInSource: $this->range([0, 5], [0, 12]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 5], [0, 7]),
                        value: AttributeName::from('foo')
                    ),
                    value: new ExpressionNode(
                        rangeInSource: $this->range([0, 10], [0, 12]),
                        root: new ValueReferenceNode(
                            rangeInSource: $this->range([0, 10], [0, 12]),
                            name: VariableName::from('bar')
                        )
                    )
                ),
                new AttributeNode(
                    rangeInSource: $this->range([0, 15], [0, 22]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 15], [0, 17]),
                        value: AttributeName::from('baz')
                    ),
                    value: new ExpressionNode(
                        rangeInSource: $this->range([0, 20], [0, 22]),
                        root: new ValueReferenceNode(
                            rangeInSource: $this->range([0, 20], [0, 22]),
                            name: VariableName::from('qux')
                        )
                    )
                ),
                new AttributeNode(
                    rangeInSource: $this->range([0, 25], [0, 35]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 25], [0, 28]),
                        value: AttributeName::from('quux')
                    ),
                    value: new ExpressionNode(
                        rangeInSource: $this->range([0, 31], [0, 35]),
                        root: new ValueReferenceNode(
                            rangeInSource: $this->range([0, 31], [0, 35]),
                            name: VariableName::from('corge')
                        )
                    )
                )
            ),
            children: new ChildNodes(),
            isSelfClosing: true
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithEmptyContentAndWithoutAttributes(): void
    {
        $tagParser = TagParser::singleton();
        $lexer = new Lexer('<a></a>');

        $expectedTagNode = new TagNode(
            rangeInSource: $this->range([0, 0], [0, 6]),
            name: new TagNameNode(
                rangeInSource: $this->range([0, 1], [0, 1]),
                value: TagName::from('a')
            ),
            attributes: new AttributeNodes(),
            children: new ChildNodes(),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function throwsIfClosingTagNameDoesNotMatchOpeningTagName(): void
    {
        $this->assertThrowsParserException(
            function () {
                $tagParser = TagParser::singleton();
                $lexer = new Lexer('<a></b>');

                $tagParser->parse($lexer);
            },
            TagCouldNotBeParsed::becauseOfClosingTagNameMismatch(
                expectedTagName: TagName::from('a'),
                actualTagName: 'b',
                affectedRangeInSource: $this->range([0, 3], [0, 6])
            )
        );
    }

    /**
     * @test
     */
    public function parsesTagWithEmptyContentAndValuelessAttribute(): void
    {
        $tagParser = TagParser::singleton();
        $lexer = new Lexer('<a foo></a>');

        $expectedTagNode = new TagNode(
            rangeInSource: $this->range([0, 0], [0, 10]),
            name: new TagNameNode(
                rangeInSource: $this->range([0, 1], [0, 1]),
                value: TagName::from('a')
            ),
            attributes: new AttributeNodes(
                new AttributeNode(
                    rangeInSource: $this->range([0, 3], [0, 5]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 3], [0, 5]),
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
            $tagParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithEmptyContentAndMultipleValuelessAttributes(): void
    {
        $tagParser = TagParser::singleton();
        $lexer = new Lexer('<a foo bar baz></a>');

        $expectedTagNode = new TagNode(
            rangeInSource: $this->range([0, 0], [0, 18]),
            name: new TagNameNode(
                rangeInSource: $this->range([0, 1], [0, 1]),
                value: TagName::from('a')
            ),
            attributes: new AttributeNodes(
                new AttributeNode(
                    rangeInSource: $this->range([0, 3], [0, 5]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 3], [0, 5]),
                        value: AttributeName::from('foo')
                    ),
                    value: null
                ),
                new AttributeNode(
                    rangeInSource: $this->range([0, 7], [0, 9]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 7], [0, 9]),
                        value: AttributeName::from('bar')
                    ),
                    value: null
                ),
                new AttributeNode(
                    rangeInSource: $this->range([0, 11], [0, 13]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 11], [0, 13]),
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
            $tagParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithEmptyContentAndStringAttribute(): void
    {
        $tagParser = TagParser::singleton();
        $lexer = new Lexer('<audio foo="bar"></audio>');

        $expectedTagNode = new TagNode(
            rangeInSource: $this->range([0, 0], [0, 24]),
            name: new TagNameNode(
                rangeInSource: $this->range([0, 1], [0, 5]),
                value: TagName::from('audio')
            ),
            attributes: new AttributeNodes(
                new AttributeNode(
                    rangeInSource: $this->range([0, 7], [0, 15]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 7], [0, 9]),
                        value: AttributeName::from('foo')
                    ),
                    value: new StringLiteralNode(
                        rangeInSource: $this->range([0, 11], [0, 15]),
                        value: 'bar'
                    )
                ),
            ),
            children: new ChildNodes(),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithEmptyContentAndMultipleStringAttributes(): void
    {
        $tagParser = TagParser::singleton();
        $lexer = new Lexer('<video foo="bar" baz="qux" quux="corge"></video>');

        $expectedTagNode = new TagNode(
            rangeInSource: $this->range([0, 0], [0, 47]),
            name: new TagNameNode(
                rangeInSource: $this->range([0, 1], [0, 5]),
                value: TagName::from('video')
            ),
            attributes: new AttributeNodes(
                new AttributeNode(
                    rangeInSource: $this->range([0, 7], [0, 15]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 7], [0, 9]),
                        value: AttributeName::from('foo')
                    ),
                    value: new StringLiteralNode(
                        rangeInSource: $this->range([0, 11], [0, 15]),
                        value: 'bar'
                    )
                ),
                new AttributeNode(
                    rangeInSource: $this->range([0, 17], [0, 25]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 17], [0, 19]),
                        value: AttributeName::from('baz')
                    ),
                    value: new StringLiteralNode(
                        rangeInSource: $this->range([0, 21], [0, 25]),
                        value: 'qux'
                    )
                ),
                new AttributeNode(
                    rangeInSource: $this->range([0, 27], [0, 38]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 27], [0, 30]),
                        value: AttributeName::from('quux')
                    ),
                    value: new StringLiteralNode(
                        rangeInSource: $this->range([0, 32], [0, 38]),
                        value: 'corge'
                    )
                ),
            ),
            children: new ChildNodes(),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithEmptyContentAndExpressionAttribute(): void
    {
        $tagParser = TagParser::singleton();
        $lexer = new Lexer('<audio foo={bar}></audio>');

        $expectedTagNode = new TagNode(
            rangeInSource: $this->range([0, 0], [0, 24]),
            name: new TagNameNode(
                rangeInSource: $this->range([0, 1], [0, 5]),
                value: TagName::from('audio')
            ),
            attributes: new AttributeNodes(
                new AttributeNode(
                    rangeInSource: $this->range([0, 7], [0, 14]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 7], [0, 9]),
                        value: AttributeName::from('foo')
                    ),
                    value: new ExpressionNode(
                        rangeInSource: $this->range([0, 12], [0, 14]),
                        root: new ValueReferenceNode(
                            rangeInSource: $this->range([0, 12], [0, 14]),
                            name: VariableName::from('bar')
                        )
                    )
                ),
            ),
            children: new ChildNodes(),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithEmptyContentAndMultipleExpressionAttributes(): void
    {
        $tagParser = TagParser::singleton();
        $lexer = new Lexer('<video foo={bar} baz={qux} quux={corge}></video>');

        $expectedTagNode = new TagNode(
            rangeInSource: $this->range([0, 0], [0, 47]),
            name: new TagNameNode(
                rangeInSource: $this->range([0, 1], [0, 5]),
                value: TagName::from('video')
            ),
            attributes: new AttributeNodes(
                new AttributeNode(
                    rangeInSource: $this->range([0, 7], [0, 14]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 7], [0, 9]),
                        value: AttributeName::from('foo')
                    ),
                    value: new ExpressionNode(
                        rangeInSource: $this->range([0, 12], [0, 14]),
                        root: new ValueReferenceNode(
                            rangeInSource: $this->range([0, 12], [0, 14]),
                            name: VariableName::from('bar')
                        )
                    )
                ),
                new AttributeNode(
                    rangeInSource: $this->range([0, 17], [0, 24]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 17], [0, 19]),
                        value: AttributeName::from('baz')
                    ),
                    value: new ExpressionNode(
                        rangeInSource: $this->range([0, 22], [0, 24]),
                        root: new ValueReferenceNode(
                            rangeInSource: $this->range([0, 22], [0, 24]),
                            name: VariableName::from('qux')
                        )
                    )
                ),
                new AttributeNode(
                    rangeInSource: $this->range([0, 27], [0, 37]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 27], [0, 30]),
                        value: AttributeName::from('quux')
                    ),
                    value: new ExpressionNode(
                        rangeInSource: $this->range([0, 33], [0, 37]),
                        root: new ValueReferenceNode(
                            rangeInSource: $this->range([0, 33], [0, 37]),
                            name: VariableName::from('corge')
                        )
                    )
                ),
            ),
            children: new ChildNodes(),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithTextContentAndWithoutAttributes(): void
    {
        $tagParser = TagParser::singleton();
        $lexer = new Lexer('<a>Lorem ipsum...</a>');

        $expectedTagNode = new TagNode(
            rangeInSource: $this->range([0, 0], [0, 20]),
            name: new TagNameNode(
                rangeInSource: $this->range([0, 1], [0, 1]),
                value: TagName::from('a')
            ),
            attributes: new AttributeNodes(),
            children: new ChildNodes(
                new TextNode(
                    rangeInSource: $this->range([0, 3], [0, 16]),
                    value: 'Lorem ipsum...'
                )
            ),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithExpressionContentAndWithoutAttributes(): void
    {
        $tagParser = TagParser::singleton();
        $lexer = new Lexer('<a>{someExpression}</a>');

        $expectedTagNode = new TagNode(
            rangeInSource: $this->range([0, 0], [0, 22]),
            name: new TagNameNode(
                rangeInSource: $this->range([0, 1], [0, 1]),
                value: TagName::from('a')
            ),
            attributes: new AttributeNodes(),
            children: new ChildNodes(
                new ExpressionNode(
                    rangeInSource: $this->range([0, 4], [0, 17]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 4], [0, 17]),
                        name: VariableName::from('someExpression')
                    )
                )
            ),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithNestedSelfClosingTagContentAndWithoutAttributes(): void
    {
        $tagParser = TagParser::singleton();
        $lexer = new Lexer('<a><b/></a>');

        $expectedTagNode = new TagNode(
            rangeInSource: $this->range([0, 0], [0, 10]),
            name: new TagNameNode(
                rangeInSource: $this->range([0, 1], [0, 1]),
                value: TagName::from('a')
            ),
            attributes: new AttributeNodes(),
            children: new ChildNodes(
                new TagNode(
                    rangeInSource: $this->range([0, 3], [0, 6]),
                    name: new TagNameNode(
                        rangeInSource: $this->range([0, 4], [0, 4]),
                        value: TagName::from('b')
                    ),
                    attributes: new AttributeNodes(),
                    children: new ChildNodes(),
                    isSelfClosing: true
                )
            ),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithNestedTagAndWithoutAttributes(): void
    {
        $tagParser = TagParser::singleton();
        $lexer = new Lexer('<a><b></b></a>');

        $expectedTagNode = new TagNode(
            rangeInSource: $this->range([0, 0], [0, 13]),
            name: new TagNameNode(
                rangeInSource: $this->range([0, 1], [0, 1]),
                value: TagName::from('a')
            ),
            attributes: new AttributeNodes(),
            children: new ChildNodes(
                new TagNode(
                    rangeInSource: $this->range([0, 3], [0, 9]),
                    name: new TagNameNode(
                        rangeInSource: $this->range([0, 4], [0, 4]),
                        value: TagName::from('b')
                    ),
                    attributes: new AttributeNodes(),
                    children: new ChildNodes(),
                    isSelfClosing: false
                )
            ),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithNestedTagsOnMultipleLevelsAndWithoutAttributes(): void
    {
        $tagParser = TagParser::singleton();
        $lexer = new Lexer('<a><b><c><d/></c></b></a>');

        $expectedTagNode = new TagNode(
            rangeInSource: $this->range([0, 0], [0, 24]),
            name: new TagNameNode(
                rangeInSource: $this->range([0, 1], [0, 1]),
                value: TagName::from('a')
            ),
            attributes: new AttributeNodes(),
            children: new ChildNodes(
                new TagNode(
                    rangeInSource: $this->range([0, 3], [0, 20]),
                    name: new TagNameNode(
                        rangeInSource: $this->range([0, 4], [0, 4]),
                        value: TagName::from('b')
                    ),
                    attributes: new AttributeNodes(),
                    children: new ChildNodes(
                        new TagNode(
                            rangeInSource: $this->range([0, 6], [0, 16]),
                            name: new TagNameNode(
                                rangeInSource: $this->range([0, 7], [0, 7]),
                                value: TagName::from('c')
                            ),
                            attributes: new AttributeNodes(),
                            children: new ChildNodes(
                                new TagNode(
                                    rangeInSource: $this->range([0, 9], [0, 12]),
                                    name: new TagNameNode(
                                        rangeInSource: $this->range([0, 10], [0, 10]),
                                        value: TagName::from('d')
                                    ),
                                    attributes: new AttributeNodes(),
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
            $tagParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithNestedTagInBetweenSpacesAndWithoutAttributes(): void
    {
        $tagParser = TagParser::singleton();
        $lexer = new Lexer('<a>   <b></b>   </a>');

        $expectedTagNode = new TagNode(
            rangeInSource: $this->range([0, 0], [0, 19]),
            name: new TagNameNode(
                rangeInSource: $this->range([0, 1], [0, 1]),
                value: TagName::from('a')
            ),
            attributes: new AttributeNodes(),
            children: new ChildNodes(
                new TagNode(
                    rangeInSource: $this->range([0, 6], [0, 12]),
                    name: new TagNameNode(
                        rangeInSource: $this->range([0, 7], [0, 7]),
                        value: TagName::from('b')
                    ),
                    attributes: new AttributeNodes(),
                    children: new ChildNodes(),
                    isSelfClosing: false
                )
            ),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithNestedTagInBetweenTextContentPreservingSpaceAroundTheNestedTag(): void
    {
        $tagParser = TagParser::singleton();
        $lexer = new Lexer('<a>Something <b>important</b> happened.</a>');

        $expectedTagNode = new TagNode(
            rangeInSource: $this->range([0, 0], [0, 42]),
            name: new TagNameNode(
                rangeInSource: $this->range([0, 1], [0, 1]),
                value: TagName::from('a')
            ),
            attributes: new AttributeNodes(),
            children: new ChildNodes(
                new TextNode(
                    rangeInSource: $this->range([0, 3], [0, 12]),
                    value: 'Something '
                ),
                new TagNode(
                    rangeInSource: $this->range([0, 13], [0, 28]),
                    name: new TagNameNode(
                        rangeInSource: $this->range([0, 14], [0, 14]),
                        value: TagName::from('b')
                    ),
                    attributes: new AttributeNodes(),
                    children: new ChildNodes(
                        new TextNode(
                            rangeInSource: $this->range([0, 16], [0, 24]),
                            value: 'important'
                        )
                    ),
                    isSelfClosing: false
                ),
                new TextNode(
                    rangeInSource: $this->range([0, 29], [0, 38]),
                    value: ' happened.'
                )
            ),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithExpressionInBetweenTextContentPreservingSpaceAroundTheExpression(): void
    {
        $tagParser = TagParser::singleton();
        $lexer = new Lexer('<a>Something {variable} happened.</a>');

        $expectedTagNode = new TagNode(
            rangeInSource: $this->range([0, 0], [0, 36]),
            name: new TagNameNode(
                rangeInSource: $this->range([0, 1], [0, 1]),
                value: TagName::from('a')
            ),
            attributes: new AttributeNodes(),
            children: new ChildNodes(
                new TextNode(
                    rangeInSource: $this->range([0, 3], [0, 12]),
                    value: 'Something '
                ),
                new ExpressionNode(
                    rangeInSource: $this->range([0, 14], [0, 21]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 14], [0, 21]),
                        name: VariableName::from('variable')
                    )
                ),
                new TextNode(
                    rangeInSource: $this->range([0, 23], [0, 32]),
                    value: ' happened.'
                )
            ),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithMultipleNestedTagsAsImmediateChildren(): void
    {
        $tagParser = TagParser::singleton();
        $lexer = new Lexer('<a><b></b><c/><d></d></a>');

        $expectedTagNode = new TagNode(
            rangeInSource: $this->range([0, 0], [0, 24]),
            name: new TagNameNode(
                rangeInSource: $this->range([0, 1], [0, 1]),
                value: TagName::from('a')
            ),
            attributes: new AttributeNodes(),
            children: new ChildNodes(
                new TagNode(
                    rangeInSource: $this->range([0, 3], [0, 9]),
                    name: new TagNameNode(
                        rangeInSource: $this->range([0, 4], [0, 4]),
                        value: TagName::from('b')
                    ),
                    attributes: new AttributeNodes(),
                    children: new ChildNodes(),
                    isSelfClosing: false
                ),
                new TagNode(
                    rangeInSource: $this->range([0, 10], [0, 13]),
                    name: new TagNameNode(
                        rangeInSource: $this->range([0, 11], [0, 11]),
                        value: TagName::from('c')
                    ),
                    attributes: new AttributeNodes(),
                    children: new ChildNodes(),
                    isSelfClosing: true
                ),
                new TagNode(
                    rangeInSource: $this->range([0, 14], [0, 20]),
                    name: new TagNameNode(
                        rangeInSource: $this->range([0, 15], [0, 15]),
                        value: TagName::from('d')
                    ),
                    attributes: new AttributeNodes(),
                    children: new ChildNodes(),
                    isSelfClosing: false
                ),
            ),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTagWithMultipleNestedTagsOnMultipleLevelsAllHavingAttributesAndContentsThemselves(): void
    {
        $tagParser = TagParser::singleton();
        $tagAsString = <<<AFX
        <div class="test" hidden>
            Some opening text
            <h1>Headline</h1>
            <a href="about:blank" target="_blank">This is a link</a>
            <p class={rte}>
                This is a {paragraph} with <em>emphasized</em> and <strong>boldened</strong> text.
            </p>
            Some closing text
        </div>
        AFX;
        $lexer = new Lexer($tagAsString);

        $expectedTagNode = new TagNode(
            rangeInSource: $this->range([0, 0], [8, 5]),
            name: new TagNameNode(
                rangeInSource: $this->range([0, 1], [0, 3]),
                value: TagName::from('div')
            ),
            attributes: new AttributeNodes(
                new AttributeNode(
                    rangeInSource: $this->range([0, 5], [0, 16]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 5], [0, 9]),
                        value: AttributeName::from('class')
                    ),
                    value: new StringLiteralNode(
                        rangeInSource: $this->range([0, 11], [0, 16]),
                        value: 'test'
                    )
                ),
                new AttributeNode(
                    rangeInSource: $this->range([0, 18], [0, 23]),
                    name: new AttributeNameNode(
                        rangeInSource: $this->range([0, 18], [0, 23]),
                        value: AttributeName::from('hidden')
                    ),
                    value: null
                ),
            ),
            children: new ChildNodes(
                new TextNode(
                    rangeInSource: $this->range([0, 25], [2, 3]),
                    value: 'Some opening text'
                ),
                new TagNode(
                    rangeInSource: $this->range([2, 4], [2, 20]),
                    name: new TagNameNode(
                        rangeInSource: $this->range([2, 5], [2, 6]),
                        value: TagName::from('h1')
                    ),
                    attributes: new AttributeNodes(),
                    children: new ChildNodes(
                        new TextNode(
                            rangeInSource: $this->range([2, 8], [2, 15]),
                            value: 'Headline'
                        ),
                    ),
                    isSelfClosing: false
                ),
                new TagNode(
                    rangeInSource: $this->range([3, 4], [3, 59]),
                    name: new TagNameNode(
                        rangeInSource: $this->range([3, 5], [3, 5]),
                        value: TagName::from('a')
                    ),
                    attributes: new AttributeNodes(
                        new AttributeNode(
                            rangeInSource: $this->range([3, 7], [3, 24]),
                            name: new AttributeNameNode(
                                rangeInSource: $this->range([3, 7], [3, 10]),
                                value: AttributeName::from('href')
                            ),
                            value: new StringLiteralNode(
                                rangeInSource: $this->range([3, 12], [3, 24]),
                                value: 'about:blank'
                            )
                        ),
                        new AttributeNode(
                            rangeInSource: $this->range([3, 26], [3, 40]),
                            name: new AttributeNameNode(
                                rangeInSource: $this->range([3, 26], [3, 31]),
                                value: AttributeName::from('target')
                            ),
                            value: new StringLiteralNode(
                                rangeInSource: $this->range([3, 33], [3, 40]),
                                value: '_blank'
                            )
                        ),
                    ),
                    children: new ChildNodes(
                        new TextNode(
                            rangeInSource: $this->range([3, 42], [3, 55]),
                            value: 'This is a link'
                        ),
                    ),
                    isSelfClosing: false
                ),
                new TagNode(
                    rangeInSource: $this->range([4, 4], [6, 7]),
                    name: new TagNameNode(
                        rangeInSource: $this->range([4, 5], [4, 5]),
                        value: TagName::from('p')
                    ),
                    attributes: new AttributeNodes(
                        new AttributeNode(
                            rangeInSource: $this->range([4, 7], [4, 16]),
                            name: new AttributeNameNode(
                                rangeInSource: $this->range([4, 7], [4, 11]),
                                value: AttributeName::from('class')
                            ),
                            value: new ExpressionNode(
                                rangeInSource: $this->range([4, 14], [4, 16]),
                                root: new ValueReferenceNode(
                                    rangeInSource: $this->range([4, 14], [4, 16]),
                                    name: VariableName::from('rte')
                                )
                            )
                        ),
                    ),
                    children: new ChildNodes(
                        new TextNode(
                            rangeInSource: $this->range([4, 19], [5, 17]),
                            value: 'This is a '
                        ),
                        new ExpressionNode(
                            rangeInSource: $this->range([5, 19], [5, 27]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([5, 19], [5, 27]),
                                name: VariableName::from('paragraph')
                            )
                        ),
                        new TextNode(
                            rangeInSource: $this->range([5, 29], [5, 34]),
                            value: ' with '
                        ),
                        new TagNode(
                            rangeInSource: $this->range([5, 35], [5, 53]),
                            name: new TagNameNode(
                                rangeInSource: $this->range([5, 36], [5, 37]),
                                value: TagName::from('em')
                            ),
                            attributes: new AttributeNodes(),
                            children: new ChildNodes(
                                new TextNode(
                                    rangeInSource: $this->range([5, 39], [5, 48]),
                                    value: 'emphasized'
                                ),
                            ),
                            isSelfClosing: false
                        ),
                        new TextNode(
                            rangeInSource: $this->range([5, 54], [5, 58]),
                            value: ' and '
                        ),
                        new TagNode(
                            rangeInSource: $this->range([5, 59], [5, 83]),
                            name: new TagNameNode(
                                rangeInSource: $this->range([5, 60], [5, 65]),
                                value: TagName::from('strong')
                            ),
                            attributes: new AttributeNodes(),
                            children: new ChildNodes(
                                new TextNode(
                                    rangeInSource: $this->range([5, 67], [5, 74]),
                                    value: 'boldened'
                                ),
                            ),
                            isSelfClosing: false
                        ),
                        new TextNode(
                            rangeInSource: $this->range([5, 84], [6, 3]),
                            value: ' text.'
                        )
                    ),
                    isSelfClosing: false
                ),
                new TextNode(
                    rangeInSource: $this->range([6, 8], [7, 21]),
                    value: 'Some closing text'
                ),
            ),
            isSelfClosing: false
        );

        $this->assertEquals(
            $expectedTagNode,
            $tagParser->parse($lexer)
        );
    }
}
