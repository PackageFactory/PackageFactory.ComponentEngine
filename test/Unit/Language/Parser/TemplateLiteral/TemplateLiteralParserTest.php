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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Parser\TemplateLiteral;

use PackageFactory\ComponentEngine\Domain\VariableName\VariableName;
use PackageFactory\ComponentEngine\Language\AST\Node\BinaryOperation\BinaryOperationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\BinaryOperation\BinaryOperator;
use PackageFactory\ComponentEngine\Language\AST\Node\Expression\ExpressionNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StringLiteral\StringLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralExpressionSegmentNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralLine;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralLines;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralSegments;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralStringSegmentNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TernaryOperation\TernaryOperationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\ValueReference\ValueReferenceNode;
use PackageFactory\ComponentEngine\Language\Lexer\Lexer;
use PackageFactory\ComponentEngine\Language\Parser\TemplateLiteral\TemplateLiteralParser;
use PackageFactory\ComponentEngine\Test\Unit\Language\Parser\ParserTestCase;

final class TemplateLiteralParserTest extends ParserTestCase
{
    /**
     * @test
     */
    public function parsesEmptyTemplateLiteral(): void
    {
        $templateLiteralParser = TemplateLiteralParser::singleton();
        $lexer = new Lexer(<<<EOF
        """
        """
        EOF);

        $expectedTemplateLiteralNode = new TemplateLiteralNode(
            rangeInSource: $this->range([0, 0], [1, 2]),
            indentation: 0,
            lines: new TemplateLiteralLines()
        );

        $this->assertEquals(
            $expectedTemplateLiteralNode,
            $templateLiteralParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTemplateLiteralWithoutEmbeddedExpressions(): void
    {
        $templateLiteralParser = TemplateLiteralParser::singleton();
        $lexer = new Lexer(<<<EOF
        """
        Hello World
        """
        EOF);

        $expectedTemplateLiteralNode = new TemplateLiteralNode(
            rangeInSource: $this->range([0, 0], [2, 2]),
            indentation: 0,
            lines: new TemplateLiteralLines(
                new TemplateLiteralLine(
                    indentation: 0,
                    segments: new TemplateLiteralSegments(
                        new TemplateLiteralStringSegmentNode(
                            rangeInSource: $this->range([1, 0], [1, 10]),
                            value: 'Hello World'
                        )
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedTemplateLiteralNode,
            $templateLiteralParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTemplateLiteralWhileCapturingFinalAndLineIndentation(): void
    {
        $templateLiteralParser = TemplateLiteralParser::singleton();
        $lexer = new Lexer(<<<EOF
        """
            Hello World
                Hello World
                  Hello World
            """
        EOF);

        $expectedTemplateLiteralNode = new TemplateLiteralNode(
            rangeInSource: $this->range([0, 0], [4, 6]),
            indentation: 4,
            lines: new TemplateLiteralLines(
                new TemplateLiteralLine(
                    indentation: 4,
                    segments: new TemplateLiteralSegments(
                        new TemplateLiteralStringSegmentNode(
                            rangeInSource: $this->range([1, 4], [1, 14]),
                            value: 'Hello World'
                        )
                    )
                ),
                new TemplateLiteralLine(
                    indentation: 8,
                    segments: new TemplateLiteralSegments(
                        new TemplateLiteralStringSegmentNode(
                            rangeInSource: $this->range([2, 8], [2, 18]),
                            value: 'Hello World'
                        )
                    )
                ),
                new TemplateLiteralLine(
                    indentation: 10,
                    segments: new TemplateLiteralSegments(
                        new TemplateLiteralStringSegmentNode(
                            rangeInSource: $this->range([3, 10], [3, 20]),
                            value: 'Hello World'
                        )
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedTemplateLiteralNode,
            $templateLiteralParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTemplateLiteralWithEmptyLines(): void
    {
        $templateLiteralParser = TemplateLiteralParser::singleton();
        $lexer = new Lexer(<<<EOF
        """

        Hello World

        """
        EOF);

        $expectedTemplateLiteralNode = new TemplateLiteralNode(
            rangeInSource: $this->range([0, 0], [4, 2]),
            indentation: 0,
            lines: new TemplateLiteralLines(
                new TemplateLiteralLine(
                    indentation: 0,
                    segments: new TemplateLiteralSegments()
                ),
                new TemplateLiteralLine(
                    indentation: 0,
                    segments: new TemplateLiteralSegments(
                        new TemplateLiteralStringSegmentNode(
                            rangeInSource: $this->range([2, 0], [2, 10]),
                            value: 'Hello World'
                        )
                    )
                ),
                new TemplateLiteralLine(
                    indentation: 0,
                    segments: new TemplateLiteralSegments()
                )
            )
        );

        $this->assertEquals(
            $expectedTemplateLiteralNode,
            $templateLiteralParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTemplateLiteralWithOnlyEmbeddedExpression(): void
    {
        $templateLiteralParser = TemplateLiteralParser::singleton();
        $lexer = new Lexer(<<<EOF
        """
        {foo}
        """
        EOF);

        $expectedTemplateLiteralNode = new TemplateLiteralNode(
            rangeInSource: $this->range([0, 0], [2, 2]),
            indentation: 0,
            lines: new TemplateLiteralLines(
                new TemplateLiteralLine(
                    indentation: 0,
                    segments: new TemplateLiteralSegments(
                        new TemplateLiteralExpressionSegmentNode(
                            rangeInSource: $this->range([1, 0], [1, 4]),
                            expression: new ExpressionNode(
                                rangeInSource: $this->range([1, 1], [1, 3]),
                                root: new ValueReferenceNode(
                                    rangeInSource: $this->range([1, 1], [1, 3]),
                                    name: VariableName::from('foo')
                                )
                            )
                        )
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedTemplateLiteralNode,
            $templateLiteralParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTemplateLiteralWithLeadingAndTrailingStringSegments(): void
    {
        $templateLiteralParser = TemplateLiteralParser::singleton();
        $lexer = new Lexer(<<<EOF
        """
        Hello {friend}!
        """
        EOF);

        $expectedTemplateLiteralNode = new TemplateLiteralNode(
            rangeInSource: $this->range([0, 0], [2, 2]),
            indentation: 0,
            lines: new TemplateLiteralLines(
                new TemplateLiteralLine(
                    indentation: 0,
                    segments: new TemplateLiteralSegments(
                        new TemplateLiteralStringSegmentNode(
                            rangeInSource: $this->range([1, 0], [1, 5]),
                            value: 'Hello '
                        ),
                        new TemplateLiteralExpressionSegmentNode(
                            rangeInSource: $this->range([1, 6], [1, 13]),
                            expression: new ExpressionNode(
                                rangeInSource: $this->range([1, 7], [1, 12]),
                                root: new ValueReferenceNode(
                                    rangeInSource: $this->range([1, 7], [1, 12]),
                                    name: VariableName::from('friend')
                                )
                            )
                        ),
                        new TemplateLiteralStringSegmentNode(
                            rangeInSource: $this->range([1, 14], [1, 14]),
                            value: '!'
                        ),
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedTemplateLiteralNode,
            $templateLiteralParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTemplateLiteralWithLeadingAndTrailingExpressionSegments(): void
    {
        $templateLiteralParser = TemplateLiteralParser::singleton();
        $lexer = new Lexer(<<<EOF
        """
        {greeting} to you, {friend}
        """
        EOF);

        $expectedTemplateLiteralNode = new TemplateLiteralNode(
            rangeInSource: $this->range([0, 0], [2, 2]),
            indentation: 0,
            lines: new TemplateLiteralLines(
                new TemplateLiteralLine(
                    indentation: 0,
                    segments: new TemplateLiteralSegments(
                        new TemplateLiteralExpressionSegmentNode(
                            rangeInSource: $this->range([1, 0], [1, 9]),
                            expression: new ExpressionNode(
                                rangeInSource: $this->range([1, 1], [1, 8]),
                                root: new ValueReferenceNode(
                                    rangeInSource: $this->range([1, 1], [1, 8]),
                                    name: VariableName::from('greeting')
                                )
                            )
                        ),
                        new TemplateLiteralStringSegmentNode(
                            rangeInSource: $this->range([1, 10], [1, 18]),
                            value: ' to you, '
                        ),
                        new TemplateLiteralExpressionSegmentNode(
                            rangeInSource: $this->range([1, 19], [1, 26]),
                            expression: new ExpressionNode(
                                rangeInSource: $this->range([1, 20], [1, 25]),
                                root: new ValueReferenceNode(
                                    rangeInSource: $this->range([1, 20], [1, 25]),
                                    name: VariableName::from('friend')
                                )
                            )
                        ),
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedTemplateLiteralNode,
            $templateLiteralParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTemplateLiteralWithComplexExpression(): void
    {
        $templateLiteralParser = TemplateLiteralParser::singleton();
        $lexer = new Lexer(<<<EOF
        """
        The result is: {a < b ? "yes" : (foo ? "perhaps" : "no")}
        """
        EOF);

        $expectedTemplateLiteralNode = new TemplateLiteralNode(
            rangeInSource: $this->range([0, 0], [2, 2]),
            indentation: 0,
            lines: new TemplateLiteralLines(
                new TemplateLiteralLine(
                    indentation: 0,
                    segments: new TemplateLiteralSegments(
                        new TemplateLiteralStringSegmentNode(
                            rangeInSource: $this->range([1, 0], [1, 14]),
                            value: 'The result is: '
                        ),
                        new TemplateLiteralExpressionSegmentNode(
                            rangeInSource: $this->range([1, 15], [1, 56]),
                            expression: new ExpressionNode(
                                rangeInSource: $this->range([1, 16], [1, 55]),
                                root: new TernaryOperationNode(
                                    condition: new ExpressionNode(
                                        rangeInSource: $this->range([1, 16], [1, 20]),
                                        root: new BinaryOperationNode(
                                            rangeInSource: $this->range([1, 16], [1, 20]),
                                            leftOperand: new ExpressionNode(
                                                rangeInSource: $this->range([1, 16], [1, 16]),
                                                root: new ValueReferenceNode(
                                                    rangeInSource: $this->range([1, 16], [1, 16]),
                                                    name: VariableName::from('a')
                                                )
                                            ),
                                            operator: BinaryOperator::LESS_THAN,
                                            rightOperand: new ExpressionNode(
                                                rangeInSource: $this->range([1, 20], [1, 20]),
                                                root: new ValueReferenceNode(
                                                    rangeInSource: $this->range([1, 20], [1, 20]),
                                                    name: VariableName::from('b')
                                                )
                                            ),
                                        )
                                    ),
                                    trueBranch: new ExpressionNode(
                                        rangeInSource: $this->range([1, 24], [1, 28]),
                                        root: new StringLiteralNode(
                                            rangeInSource: $this->range([1, 24], [1, 28]),
                                            value: 'yes'
                                        )
                                    ),
                                    falseBranch: new ExpressionNode(
                                        rangeInSource: $this->range([1, 32], [1, 55]),
                                        root: new TernaryOperationNode(
                                            condition: new ExpressionNode(
                                                rangeInSource: $this->range([1, 33], [1, 35]),
                                                root: new ValueReferenceNode(
                                                    rangeInSource: $this->range([1, 33], [1, 35]),
                                                    name: VariableName::from('foo')
                                                ),
                                            ),
                                            trueBranch: new ExpressionNode(
                                                rangeInSource: $this->range([1, 39], [1, 47]),
                                                root: new StringLiteralNode(
                                                    rangeInSource: $this->range([1, 39], [1, 47]),
                                                    value: 'perhaps'
                                                )
                                            ),
                                            falseBranch: new ExpressionNode(
                                                rangeInSource: $this->range([1, 51], [1, 54]),
                                                root: new StringLiteralNode(
                                                    rangeInSource: $this->range([1, 51], [1, 54]),
                                                    value: 'no'
                                                )
                                            )
                                        )
                                    )
                                )
                            )
                        ),
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedTemplateLiteralNode,
            $templateLiteralParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTemplateLiteralWithEmbeddedTemplateLiteral(): void
    {
        $templateLiteralParser = TemplateLiteralParser::singleton();
        $lexer = new Lexer(<<<EOF
        """
        Lorem {"""
            ipsum {foo} sit
            """} amet
        """
        EOF);

        $expectedTemplateLiteralNode = new TemplateLiteralNode(
            rangeInSource: $this->range([0, 0], [4, 2]),
            indentation: 0,
            lines: new TemplateLiteralLines(
                new TemplateLiteralLine(
                    indentation: 0,
                    segments: new TemplateLiteralSegments(
                        new TemplateLiteralStringSegmentNode(
                            rangeInSource: $this->range([1, 0], [1, 5]),
                            value: 'Lorem '
                        ),
                        new TemplateLiteralExpressionSegmentNode(
                            rangeInSource: $this->range([1, 6], [3, 7]),
                            expression: new ExpressionNode(
                                rangeInSource: $this->range([1, 7], [3, 6]),
                                root: new TemplateLiteralNode(
                                    rangeInSource: $this->range([1, 7], [3, 6]),
                                    indentation: 4,
                                    lines: new TemplateLiteralLines(
                                        new TemplateLiteralLine(
                                            indentation: 4,
                                            segments: new TemplateLiteralSegments(
                                                new TemplateLiteralStringSegmentNode(
                                                    rangeInSource: $this->range([2, 4], [2, 9]),
                                                    value: 'ipsum '
                                                ),
                                                new TemplateLiteralExpressionSegmentNode(
                                                    rangeInSource: $this->range([2, 10], [2, 14]),
                                                    expression: new ExpressionNode(
                                                        rangeInSource: $this->range([2, 11], [2, 13]),
                                                        root: new ValueReferenceNode(
                                                            rangeInSource: $this->range([2, 11], [2, 13]),
                                                            name: VariableName::from('foo')
                                                        )
                                                    )
                                                ),
                                                new TemplateLiteralStringSegmentNode(
                                                    rangeInSource: $this->range([2, 15], [2, 18]),
                                                    value: ' sit'
                                                )
                                            )
                                        )
                                    )
                                )
                            )
                        ),
                        new TemplateLiteralStringSegmentNode(
                            rangeInSource: $this->range([3, 8], [3, 12]),
                            value: ' amet'
                        )
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedTemplateLiteralNode,
            $templateLiteralParser->parse($lexer)
        );
    }
}
