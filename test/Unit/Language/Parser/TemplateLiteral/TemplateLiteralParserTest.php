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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Parser\TemplateLiteral;

use PackageFactory\ComponentEngine\Domain\VariableName\VariableName;
use PackageFactory\ComponentEngine\Language\AST\Node\BinaryOperation\BinaryOperationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\BinaryOperation\BinaryOperator;
use PackageFactory\ComponentEngine\Language\AST\Node\Expression\ExpressionNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StringLiteral\StringLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralExpressionSegmentNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralSegments;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralStringSegmentNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TernaryOperation\TernaryOperationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\ValueReference\ValueReferenceNode;
use PackageFactory\ComponentEngine\Language\Parser\TemplateLiteral\TemplateLiteralParser;
use PackageFactory\ComponentEngine\Test\Unit\Language\Parser\ParserTestCase;

final class TemplateLiteralParserTest extends ParserTestCase
{
    /**
     * @test
     */
    public function parsesTemplateLiteralWithoutEmbeddedExpressions(): void
    {
        $templateLiteralParser = new TemplateLiteralParser();
        $tokens = $this->createTokenIterator('`Hello World`');

        $expectedTemplateLiteralNode = new TemplateLiteralNode(
            rangeInSource: $this->range([0, 0], [0, 12]),
            segments: new TemplateLiteralSegments(
                new TemplateLiteralStringSegmentNode(
                    rangeInSource: $this->range([0, 1], [0, 11]),
                    value: 'Hello World'
                )
            )
        );

        $this->assertEquals(
            $expectedTemplateLiteralNode,
            $templateLiteralParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesTemplateLiteralWithOnlyEmbeddedExpression(): void
    {
        $templateLiteralParser = new TemplateLiteralParser();
        $tokens = $this->createTokenIterator('`${foo}`');

        $expectedTemplateLiteralNode = new TemplateLiteralNode(
            rangeInSource: $this->range([0, 0], [0, 7]),
            segments: new TemplateLiteralSegments(
                new TemplateLiteralExpressionSegmentNode(
                    rangeInSource: $this->range([0, 1], [0, 6]),
                    expression: new ExpressionNode(
                        rangeInSource: $this->range([0, 3], [0, 5]),
                        root: new ValueReferenceNode(
                            rangeInSource: $this->range([0, 3], [0, 5]),
                            name: VariableName::from('foo')
                        )
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedTemplateLiteralNode,
            $templateLiteralParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesTemplateLiteralWithLeadingAndTrailingStringSegments(): void
    {
        $templateLiteralParser = new TemplateLiteralParser();
        $tokens = $this->createTokenIterator('`Hello ${friend}!`');

        $expectedTemplateLiteralNode = new TemplateLiteralNode(
            rangeInSource: $this->range([0, 0], [0, 17]),
            segments: new TemplateLiteralSegments(
                new TemplateLiteralStringSegmentNode(
                    rangeInSource: $this->range([0, 1], [0, 6]),
                    value: 'Hello '
                ),
                new TemplateLiteralExpressionSegmentNode(
                    rangeInSource: $this->range([0, 7], [0, 15]),
                    expression: new ExpressionNode(
                        rangeInSource: $this->range([0, 9], [0, 14]),
                        root: new ValueReferenceNode(
                            rangeInSource: $this->range([0, 9], [0, 14]),
                            name: VariableName::from('friend')
                        )
                    )
                ),
                new TemplateLiteralStringSegmentNode(
                    rangeInSource: $this->range([0, 16], [0, 16]),
                    value: '!'
                ),
            )
        );

        $this->assertEquals(
            $expectedTemplateLiteralNode,
            $templateLiteralParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesTemplateLiteralWithLeadingAndTrailingExpressionSegments(): void
    {
        $templateLiteralParser = new TemplateLiteralParser();
        $tokens = $this->createTokenIterator('`${greeting} to you, ${friend}`');

        $expectedTemplateLiteralNode = new TemplateLiteralNode(
            rangeInSource: $this->range([0, 0], [0, 30]),
            segments: new TemplateLiteralSegments(
                new TemplateLiteralExpressionSegmentNode(
                    rangeInSource: $this->range([0, 1], [0, 11]),
                    expression: new ExpressionNode(
                        rangeInSource: $this->range([0, 3], [0, 10]),
                        root: new ValueReferenceNode(
                            rangeInSource: $this->range([0, 3], [0, 10]),
                            name: VariableName::from('greeting')
                        )
                    )
                ),
                new TemplateLiteralStringSegmentNode(
                    rangeInSource: $this->range([0, 12], [0, 20]),
                    value: ' to you, '
                ),
                new TemplateLiteralExpressionSegmentNode(
                    rangeInSource: $this->range([0, 21], [0, 29]),
                    expression: new ExpressionNode(
                        rangeInSource: $this->range([0, 23], [0, 28]),
                        root: new ValueReferenceNode(
                            rangeInSource: $this->range([0, 23], [0, 28]),
                            name: VariableName::from('friend')
                        )
                    )
                ),
            )
        );

        $this->assertEquals(
            $expectedTemplateLiteralNode,
            $templateLiteralParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesTemplateLiteralWithComplexExpression(): void
    {
        $templateLiteralParser = new TemplateLiteralParser();
        $tokens = $this->createTokenIterator(
            '`The result is: ${a < b ? "yes" : (foo ? "maybe" : "no")}`'
        );

        $expectedTemplateLiteralNode = new TemplateLiteralNode(
            rangeInSource: $this->range([0, 0], [0, 57]),
            segments: new TemplateLiteralSegments(
                new TemplateLiteralStringSegmentNode(
                    rangeInSource: $this->range([0, 1], [0, 15]),
                    value: 'The result is: '
                ),
                new TemplateLiteralExpressionSegmentNode(
                    rangeInSource: $this->range([0, 16], [0, 56]),
                    expression: new ExpressionNode(
                        rangeInSource: $this->range([0, 18], [0, 55]),
                        root: new TernaryOperationNode(
                            condition: new ExpressionNode(
                                rangeInSource: $this->range([0, 18], [0, 22]),
                                root: new BinaryOperationNode(
                                    rangeInSource: $this->range([0, 18], [0, 22]),
                                    leftOperand: new ExpressionNode(
                                        rangeInSource: $this->range([0, 18], [0, 18]),
                                        root: new ValueReferenceNode(
                                            rangeInSource: $this->range([0, 18], [0, 18]),
                                            name: VariableName::from('a')
                                        )
                                    ),
                                    operator: BinaryOperator::LESS_THAN,
                                    rightOperand: new ExpressionNode(
                                        rangeInSource: $this->range([0, 22], [0, 22]),
                                        root: new ValueReferenceNode(
                                            rangeInSource: $this->range([0, 22], [0, 22]),
                                            name: VariableName::from('b')
                                        )
                                    ),
                                )
                            ),
                            trueBranch: new ExpressionNode(
                                rangeInSource: $this->range([0, 27], [0, 29]),
                                root: new StringLiteralNode(
                                    rangeInSource: $this->range([0, 27], [0, 29]),
                                    value: 'yes'
                                )
                            ),
                            falseBranch: new ExpressionNode(
                                rangeInSource: $this->range([0, 34], [0, 55]),
                                root: new TernaryOperationNode(
                                    condition: new ExpressionNode(
                                        rangeInSource: $this->range([0, 35], [0, 37]),
                                        root: new ValueReferenceNode(
                                            rangeInSource: $this->range([0, 35], [0, 37]),
                                            name: VariableName::from('foo')
                                        ),
                                    ),
                                    trueBranch: new ExpressionNode(
                                        rangeInSource: $this->range([0, 42], [0, 46]),
                                        root: new StringLiteralNode(
                                            rangeInSource: $this->range([0, 42], [0, 46]),
                                            value: 'maybe'
                                        )
                                    ),
                                    falseBranch: new ExpressionNode(
                                        rangeInSource: $this->range([0, 52], [0, 53]),
                                        root: new StringLiteralNode(
                                            rangeInSource: $this->range([0, 52], [0, 53]),
                                            value: 'no'
                                        )
                                    )
                                )
                            )
                        )
                    )
                ),
            )
        );

        $this->assertEquals(
            $expectedTemplateLiteralNode,
            $templateLiteralParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesTemplateLiteralWithEmbeddedTemplateLiteral(): void
    {
        $templateLiteralParser = new TemplateLiteralParser();
        $tokens = $this->createTokenIterator('`Lorem ${`ipsum ${foo} sit`} amet`');

        $expectedTemplateLiteralNode = new TemplateLiteralNode(
            rangeInSource: $this->range([0, 0], [0, 33]),
            segments: new TemplateLiteralSegments(
                new TemplateLiteralStringSegmentNode(
                    rangeInSource: $this->range([0, 1], [0, 6]),
                    value: 'Lorem '
                ),
                new TemplateLiteralExpressionSegmentNode(
                    rangeInSource: $this->range([0, 7], [0, 27]),
                    expression: new ExpressionNode(
                        rangeInSource: $this->range([0, 9], [0, 26]),
                        root: new TemplateLiteralNode(
                            rangeInSource: $this->range([0, 9], [0, 26]),
                            segments: new TemplateLiteralSegments(
                                new TemplateLiteralStringSegmentNode(
                                    rangeInSource: $this->range([0, 10], [0, 15]),
                                    value: 'ipsum '
                                ),
                                new TemplateLiteralExpressionSegmentNode(
                                    rangeInSource: $this->range([0, 16], [0, 21]),
                                    expression: new ExpressionNode(
                                        rangeInSource: $this->range([0, 18], [0, 20]),
                                        root: new ValueReferenceNode(
                                            rangeInSource: $this->range([0, 18], [0, 20]),
                                            name: VariableName::from('foo')
                                        )
                                    )
                                ),
                                new TemplateLiteralStringSegmentNode(
                                    rangeInSource: $this->range([0, 22], [0, 25]),
                                    value: ' sit'
                                )
                            )
                        )
                    )
                ),
                new TemplateLiteralStringSegmentNode(
                    rangeInSource: $this->range([0, 28], [0, 32]),
                    value: ' amet'
                )
            )
        );

        $this->assertEquals(
            $expectedTemplateLiteralNode,
            $templateLiteralParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function toleratesIsolatedDollarSigns(): void
    {
        $this->markTestSkipped('@TODO: This will require significant redesign of the tokenizer.');

        // $templateLiteralParser = new TemplateLiteralParser();
        // $tokens = $this->createTokenIterator('`$$$$$$$$`');

        // $expectedTemplateLiteralNode = new TemplateLiteralNode(
        //     rangeInSource: $this->range([0, 0], [0, 9]),
        //     segments: new TemplateLiteralSegments(
        //         new TemplateLiteralStringSegmentNode(
        //             rangeInSource: $this->range([0, 1], [0, 8]),
        //             value: '$$$$$$$$'
        //         )
        //     )
        // );

        // $this->assertEquals(
        //     $expectedTemplateLiteralNode,
        //     $templateLiteralParser->parse($tokens)
        // );
    }
}
