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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Parser\Expression;

use PackageFactory\ComponentEngine\Domain\AttributeName\AttributeName;
use PackageFactory\ComponentEngine\Domain\PropertyName\PropertyName;
use PackageFactory\ComponentEngine\Domain\TagName\TagName;
use PackageFactory\ComponentEngine\Domain\VariableName\VariableName;
use PackageFactory\ComponentEngine\Language\AST\Node\Access\AccessKeyNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Access\AccessNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Access\AccessType;
use PackageFactory\ComponentEngine\Language\AST\Node\BinaryOperation\BinaryOperationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\BinaryOperation\BinaryOperator;
use PackageFactory\ComponentEngine\Language\AST\Node\BooleanLiteral\BooleanLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Expression\ExpressionNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Expression\ExpressionNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\IntegerLiteral\IntegerFormat;
use PackageFactory\ComponentEngine\Language\AST\Node\IntegerLiteral\IntegerLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Match\MatchArmNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Match\MatchArmNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Match\MatchNode;
use PackageFactory\ComponentEngine\Language\AST\Node\NullLiteral\NullLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StringLiteral\StringLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\AttributeNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\AttributeNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\AttributeNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\ChildNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\TagNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\TagNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralExpressionSegmentNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralLine;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralLines;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralSegments;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralStringSegmentNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TernaryOperation\TernaryOperationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Text\TextNode;
use PackageFactory\ComponentEngine\Language\AST\Node\UnaryOperation\UnaryOperationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\UnaryOperation\UnaryOperator;
use PackageFactory\ComponentEngine\Language\AST\Node\ValueReference\ValueReferenceNode;
use PackageFactory\ComponentEngine\Language\Lexer\Lexer;
use PackageFactory\ComponentEngine\Language\Parser\Expression\ExpressionParser;
use PackageFactory\ComponentEngine\Test\Unit\Language\Parser\ParserTestCase;

final class ExpressionParserTest extends ParserTestCase
{
    /**
     * @test
     */
    public function parsesMandatoryAccessWithOneLevel(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('a.b');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 2]),
            root: new AccessNode(
                rangeInSource: $this->range([0, 0], [0, 2]),
                parent: new ExpressionNode(
                    rangeInSource: $this->range([0, 0], [0, 0]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 0], [0, 0]),
                        name: VariableName::from('a')
                    )
                ),
                type: AccessType::MANDATORY,
                key: new AccessKeyNode(
                    rangeInSource: $this->range([0, 2], [0, 2]),
                    value: PropertyName::from('b')
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesMandatoryAccessWithMultipleLevels(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('a.b.c.d.e');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 8]),
            root: new AccessNode(
                rangeInSource: $this->range([0, 0], [0, 8]),
                parent: new ExpressionNode(
                    rangeInSource: $this->range([0, 0], [0, 6]),
                    root: new AccessNode(
                        rangeInSource: $this->range([0, 0], [0, 6]),
                        parent: new ExpressionNode(
                            rangeInSource: $this->range([0, 0], [0, 4]),
                            root: new AccessNode(
                                rangeInSource: $this->range([0, 0], [0, 4]),
                                parent: new ExpressionNode(
                                    rangeInSource: $this->range([0, 0], [0, 2]),
                                    root: new AccessNode(
                                        rangeInSource: $this->range([0, 0], [0, 2]),
                                        parent: new ExpressionNode(
                                            rangeInSource: $this->range([0, 0], [0, 0]),
                                            root: new ValueReferenceNode(
                                                rangeInSource: $this->range([0, 0], [0, 0]),
                                                name: VariableName::from('a')
                                            )
                                        ),
                                        type: AccessType::MANDATORY,
                                        key: new AccessKeyNode(
                                            rangeInSource: $this->range([0, 2], [0, 2]),
                                            value: PropertyName::from('b')
                                        )
                                    )
                                ),
                                type: AccessType::MANDATORY,
                                key: new AccessKeyNode(
                                    rangeInSource: $this->range([0, 4], [0, 4]),
                                    value: PropertyName::from('c')
                                )
                            )
                        ),
                        type: AccessType::MANDATORY,
                        key: new AccessKeyNode(
                            rangeInSource: $this->range([0, 6], [0, 6]),
                            value: PropertyName::from('d')
                        )
                    )
                ),
                type: AccessType::MANDATORY,
                key: new AccessKeyNode(
                    rangeInSource: $this->range([0, 8], [0, 8]),
                    value: PropertyName::from('e')
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesOptionalAccessWithOneLevel(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('a?.b');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 3]),
            root: new AccessNode(
                rangeInSource: $this->range([0, 0], [0, 3]),
                parent: new ExpressionNode(
                    rangeInSource: $this->range([0, 0], [0, 0]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 0], [0, 0]),
                        name: VariableName::from('a')
                    )
                ),
                type: AccessType::OPTIONAL,
                key: new AccessKeyNode(
                    rangeInSource: $this->range([0, 3], [0, 3]),
                    value: PropertyName::from('b')
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesOptionalAccessWithMultipleLevels(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('a?.b?.c?.d?.e');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 12]),
            root: new AccessNode(
                rangeInSource: $this->range([0, 0], [0, 12]),
                parent: new ExpressionNode(
                    rangeInSource: $this->range([0, 0], [0, 9]),
                    root: new AccessNode(
                        rangeInSource: $this->range([0, 0], [0, 9]),
                        parent: new ExpressionNode(
                            rangeInSource: $this->range([0, 0], [0, 6]),
                            root: new AccessNode(
                                rangeInSource: $this->range([0, 0], [0, 6]),
                                parent: new ExpressionNode(
                                    rangeInSource: $this->range([0, 0], [0, 3]),
                                    root: new AccessNode(
                                        rangeInSource: $this->range([0, 0], [0, 3]),
                                        parent: new ExpressionNode(
                                            rangeInSource: $this->range([0, 0], [0, 0]),
                                            root: new ValueReferenceNode(
                                                rangeInSource: $this->range([0, 0], [0, 0]),
                                                name: VariableName::from('a')
                                            )
                                        ),
                                        type: AccessType::OPTIONAL,
                                        key: new AccessKeyNode(
                                            rangeInSource: $this->range([0, 3], [0, 3]),
                                            value: PropertyName::from('b')
                                        )
                                    )
                                ),
                                type: AccessType::OPTIONAL,
                                key: new AccessKeyNode(
                                    rangeInSource: $this->range([0, 6], [0, 6]),
                                    value: PropertyName::from('c')
                                )
                            )
                        ),
                        type: AccessType::OPTIONAL,
                        key: new AccessKeyNode(
                            rangeInSource: $this->range([0, 9], [0, 9]),
                            value: PropertyName::from('d')
                        )
                    )
                ),
                type: AccessType::OPTIONAL,
                key: new AccessKeyNode(
                    rangeInSource: $this->range([0, 12], [0, 12]),
                    value: PropertyName::from('e')
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesMixedAccessChainStartingWithMandatoryAccess(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('a.b?.c');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 5]),
            root: new AccessNode(
                rangeInSource: $this->range([0, 0], [0, 5]),
                parent: new ExpressionNode(
                    rangeInSource: $this->range([0, 0], [0, 2]),
                    root: new AccessNode(
                        rangeInSource: $this->range([0, 0], [0, 2]),
                        parent: new ExpressionNode(
                            rangeInSource: $this->range([0, 0], [0, 0]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 0], [0, 0]),
                                name: VariableName::from('a')
                            )
                        ),
                        type: AccessType::MANDATORY,
                        key: new AccessKeyNode(
                            rangeInSource: $this->range([0, 2], [0, 2]),
                            value: PropertyName::from('b')
                        )
                    )
                ),
                type: AccessType::OPTIONAL,
                key: new AccessKeyNode(
                    rangeInSource: $this->range([0, 5], [0, 5]),
                    value: PropertyName::from('c')
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesMixedAccessChainStartingWithOptionalAccess(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('a?.b.c');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 5]),
            root: new AccessNode(
                rangeInSource: $this->range([0, 0], [0, 5]),
                parent: new ExpressionNode(
                    rangeInSource: $this->range([0, 0], [0, 3]),
                    root: new AccessNode(
                        rangeInSource: $this->range([0, 0], [0, 3]),
                        parent: new ExpressionNode(
                            rangeInSource: $this->range([0, 0], [0, 0]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 0], [0, 0]),
                                name: VariableName::from('a')
                            )
                        ),
                        type: AccessType::OPTIONAL,
                        key: new AccessKeyNode(
                            rangeInSource: $this->range([0, 3], [0, 3]),
                            value: PropertyName::from('b')
                        )
                    )
                ),
                type: AccessType::MANDATORY,
                key: new AccessKeyNode(
                    rangeInSource: $this->range([0, 5], [0, 5]),
                    value: PropertyName::from('c')
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesMandatoryAccessWithBracketedEpxressionAsParent(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('(a ? b : c).d');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 12]),
            root: new AccessNode(
                rangeInSource: $this->range([0, 0], [0, 12]),
                parent: new ExpressionNode(
                    rangeInSource: $this->range([0, 0], [0, 10]),
                    root: new TernaryOperationNode(
                        condition: new ExpressionNode(
                            rangeInSource: $this->range([0, 1], [0, 1]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 1], [0, 1]),
                                name: VariableName::from('a')
                            )
                        ),
                        trueBranch: new ExpressionNode(
                            rangeInSource: $this->range([0, 5], [0, 5]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 5], [0, 5]),
                                name: VariableName::from('b')
                            )
                        ),
                        falseBranch: new ExpressionNode(
                            rangeInSource: $this->range([0, 9], [0, 9]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 9], [0, 9]),
                                name: VariableName::from('c')
                            )
                        )
                    )
                ),
                type: AccessType::MANDATORY,
                key: new AccessKeyNode(
                    rangeInSource: $this->range([0, 12], [0, 12]),
                    value: PropertyName::from('d')
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesOptionalAccessWithBracketedEpxressionAsParent(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('(a ? b : c)?.d');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 13]),
            root: new AccessNode(
                rangeInSource: $this->range([0, 0], [0, 13]),
                parent: new ExpressionNode(
                    rangeInSource: $this->range([0, 0], [0, 10]),
                    root: new TernaryOperationNode(
                        condition: new ExpressionNode(
                            rangeInSource: $this->range([0, 1], [0, 1]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 1], [0, 1]),
                                name: VariableName::from('a')
                            )
                        ),
                        trueBranch: new ExpressionNode(
                            rangeInSource: $this->range([0, 5], [0, 5]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 5], [0, 5]),
                                name: VariableName::from('b')
                            )
                        ),
                        falseBranch: new ExpressionNode(
                            rangeInSource: $this->range([0, 9], [0, 9]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 9], [0, 9]),
                                name: VariableName::from('c')
                            )
                        )
                    )
                ),
                type: AccessType::OPTIONAL,
                key: new AccessKeyNode(
                    rangeInSource: $this->range([0, 13], [0, 13]),
                    value: PropertyName::from('d')
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesBinaryOperationAnd(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('a && b');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 5]),
            root: new BinaryOperationNode(
                rangeInSource: $this->range([0, 0], [0, 5]),
                leftOperand: new ExpressionNode(
                    rangeInSource: $this->range([0, 0], [0, 0]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 0], [0, 0]),
                        name: VariableName::from('a')
                    )
                ),
                operator: BinaryOperator::AND,
                rightOperand: new ExpressionNode(
                    rangeInSource: $this->range([0, 5], [0, 5]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 5], [0, 5]),
                        name: VariableName::from('b')
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesBinaryOperationOr(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('a || b');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 5]),
            root: new BinaryOperationNode(
                rangeInSource: $this->range([0, 0], [0, 5]),
                leftOperand: new ExpressionNode(
                    rangeInSource: $this->range([0, 0], [0, 0]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 0], [0, 0]),
                        name: VariableName::from('a')
                    )
                ),
                operator: BinaryOperator::OR,
                rightOperand: new ExpressionNode(
                    rangeInSource: $this->range([0, 5], [0, 5]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 5], [0, 5]),
                        name: VariableName::from('b')
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesBinaryOperationEquals(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('a === b');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 6]),
            root: new BinaryOperationNode(
                rangeInSource: $this->range([0, 0], [0, 6]),
                leftOperand: new ExpressionNode(
                    rangeInSource: $this->range([0, 0], [0, 0]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 0], [0, 0]),
                        name: VariableName::from('a')
                    )
                ),
                operator: BinaryOperator::EQUAL,
                rightOperand: new ExpressionNode(
                    rangeInSource: $this->range([0, 6], [0, 6]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 6], [0, 6]),
                        name: VariableName::from('b')
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesBinaryOperationNotEquals(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('a !== b');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 6]),
            root: new BinaryOperationNode(
                rangeInSource: $this->range([0, 0], [0, 6]),
                leftOperand: new ExpressionNode(
                    rangeInSource: $this->range([0, 0], [0, 0]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 0], [0, 0]),
                        name: VariableName::from('a')
                    )
                ),
                operator: BinaryOperator::NOT_EQUAL,
                rightOperand: new ExpressionNode(
                    rangeInSource: $this->range([0, 6], [0, 6]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 6], [0, 6]),
                        name: VariableName::from('b')
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesBinaryOperationGreaterThan(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('a > b');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 4]),
            root: new BinaryOperationNode(
                rangeInSource: $this->range([0, 0], [0, 4]),
                leftOperand: new ExpressionNode(
                    rangeInSource: $this->range([0, 0], [0, 0]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 0], [0, 0]),
                        name: VariableName::from('a')
                    )
                ),
                operator: BinaryOperator::GREATER_THAN,
                rightOperand: new ExpressionNode(
                    rangeInSource: $this->range([0, 4], [0, 4]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 4], [0, 4]),
                        name: VariableName::from('b')
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesBinaryOperationGreaterThanOrEqual(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('a >= b');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 5]),
            root: new BinaryOperationNode(
                rangeInSource: $this->range([0, 0], [0, 5]),
                leftOperand: new ExpressionNode(
                    rangeInSource: $this->range([0, 0], [0, 0]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 0], [0, 0]),
                        name: VariableName::from('a')
                    )
                ),
                operator: BinaryOperator::GREATER_THAN_OR_EQUAL,
                rightOperand: new ExpressionNode(
                    rangeInSource: $this->range([0, 5], [0, 5]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 5], [0, 5]),
                        name: VariableName::from('b')
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesBinaryOperationLessThan(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('a < b');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 4]),
            root: new BinaryOperationNode(
                rangeInSource: $this->range([0, 0], [0, 4]),
                leftOperand: new ExpressionNode(
                    rangeInSource: $this->range([0, 0], [0, 0]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 0], [0, 0]),
                        name: VariableName::from('a')
                    )
                ),
                operator: BinaryOperator::LESS_THAN,
                rightOperand: new ExpressionNode(
                    rangeInSource: $this->range([0, 4], [0, 4]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 4], [0, 4]),
                        name: VariableName::from('b')
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesBinaryOperationLessThanOrEqual(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('a <= b');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 5]),
            root: new BinaryOperationNode(
                rangeInSource: $this->range([0, 0], [0, 5]),
                leftOperand: new ExpressionNode(
                    rangeInSource: $this->range([0, 0], [0, 0]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 0], [0, 0]),
                        name: VariableName::from('a')
                    )
                ),
                operator: BinaryOperator::LESS_THAN_OR_EQUAL,
                rightOperand: new ExpressionNode(
                    rangeInSource: $this->range([0, 5], [0, 5]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 5], [0, 5]),
                        name: VariableName::from('b')
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesBinaryOperationInBrackets(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('(a <= b)');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 7]),
            root: new BinaryOperationNode(
                rangeInSource: $this->range([0, 1], [0, 6]),
                leftOperand: new ExpressionNode(
                    rangeInSource: $this->range([0, 1], [0, 1]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 1], [0, 1]),
                        name: VariableName::from('a')
                    )
                ),
                operator: BinaryOperator::LESS_THAN_OR_EQUAL,
                rightOperand: new ExpressionNode(
                    rangeInSource: $this->range([0, 6], [0, 6]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 6], [0, 6]),
                        name: VariableName::from('b')
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesBinaryOperationInMultipleBrackets(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('((((a <= b))))');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 13]),
            root: new BinaryOperationNode(
                rangeInSource: $this->range([0, 4], [0, 9]),
                leftOperand: new ExpressionNode(
                    rangeInSource: $this->range([0, 4], [0, 4]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 4], [0, 4]),
                        name: VariableName::from('a')
                    )
                ),
                operator: BinaryOperator::LESS_THAN_OR_EQUAL,
                rightOperand: new ExpressionNode(
                    rangeInSource: $this->range([0, 9], [0, 9]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 9], [0, 9]),
                        name: VariableName::from('b')
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesBooleanLiteralTrue(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('true');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 3]),
            root: new BooleanLiteralNode(
                rangeInSource: $this->range([0, 0], [0, 3]),
                value: true
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesBooleanLiteralFalse(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('false');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 4]),
            root: new BooleanLiteralNode(
                rangeInSource: $this->range([0, 0], [0, 4]),
                value: false
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesBinaryIntegerLiteral(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('0b1001');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 5]),
            root: new IntegerLiteralNode(
                rangeInSource: $this->range([0, 0], [0, 5]),
                format: IntegerFormat::BINARY,
                value: '0b1001'
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesOctalIntegerLiteral(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('0o755');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 4]),
            root: new IntegerLiteralNode(
                rangeInSource: $this->range([0, 0], [0, 4]),
                format: IntegerFormat::OCTAL,
                value: '0o755'
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesDecimalIntegerLiteral(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('42');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 1]),
            root: new IntegerLiteralNode(
                rangeInSource: $this->range([0, 0], [0, 1]),
                format: IntegerFormat::DECIMAL,
                value: '42'
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesHexadecimalIntegerLiteral(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('0xABC');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 4]),
            root: new IntegerLiteralNode(
                rangeInSource: $this->range([0, 0], [0, 4]),
                format: IntegerFormat::HEXADECIMAL,
                value: '0xABC'
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesMatch(): void
    {
        $expressionParser = new ExpressionParser();
        $matchAsString = <<<AFX
        match foo.bar?.baz {
            Qux.QUUX,
            Qux.CORGE -> foo
            null -> foo.bar
            default -> "N/A"
        }
        AFX;
        $lexer = new Lexer($matchAsString);

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [5, 0]),
            root: new MatchNode(
                rangeInSource: $this->range([0, 0], [5, 0]),
                subject: new ExpressionNode(
                    rangeInSource: $this->range([0, 6], [0, 17]),
                    root: new AccessNode(
                        rangeInSource: $this->range([0, 6], [0, 17]),
                        parent: new ExpressionNode(
                            rangeInSource: $this->range([0, 6], [0, 12]),
                            root: new AccessNode(
                                rangeInSource: $this->range([0, 6], [0, 12]),
                                parent: new ExpressionNode(
                                    rangeInSource: $this->range([0, 6], [0, 8]),
                                    root: new ValueReferenceNode(
                                        rangeInSource: $this->range([0, 6], [0, 8]),
                                        name: VariableName::from('foo')
                                    )
                                ),
                                type: AccessType::MANDATORY,
                                key: new AccessKeyNode(
                                    rangeInSource: $this->range([0, 10], [0, 12]),
                                    value: PropertyName::from('bar')
                                )
                            )
                        ),
                        type: AccessType::OPTIONAL,
                        key: new AccessKeyNode(
                            rangeInSource: $this->range([0, 15], [0, 17]),
                            value: PropertyName::from('baz')
                        )
                    )
                ),
                arms: new MatchArmNodes(
                    new MatchArmNode(
                        rangeInSource: $this->range([1, 4], [2, 19]),
                        left: new ExpressionNodes(
                            new ExpressionNode(
                                rangeInSource: $this->range([1, 4], [1, 11]),
                                root: new AccessNode(
                                    rangeInSource: $this->range([1, 4], [1, 11]),
                                    parent: new ExpressionNode(
                                        rangeInSource: $this->range([1, 4], [1, 6]),
                                        root: new ValueReferenceNode(
                                            rangeInSource: $this->range([1, 4], [1, 6]),
                                            name: VariableName::from('Qux')
                                        )
                                    ),
                                    type: AccessType::MANDATORY,
                                    key: new AccessKeyNode(
                                        rangeInSource: $this->range([1, 8], [1, 11]),
                                        value: PropertyName::from('QUUX')
                                    )
                                )
                            ),
                            new ExpressionNode(
                                rangeInSource: $this->range([2, 4], [2, 12]),
                                root: new AccessNode(
                                    rangeInSource: $this->range([2, 4], [2, 12]),
                                    parent: new ExpressionNode(
                                        rangeInSource: $this->range([2, 4], [2, 6]),
                                        root: new ValueReferenceNode(
                                            rangeInSource: $this->range([2, 4], [2, 6]),
                                            name: VariableName::from('Qux')
                                        )
                                    ),
                                    type: AccessType::MANDATORY,
                                    key: new AccessKeyNode(
                                        rangeInSource: $this->range([2, 8], [2, 12]),
                                        value: PropertyName::from('CORGE')
                                    )
                                )
                            ),
                        ),
                        right: new ExpressionNode(
                            rangeInSource: $this->range([2, 17], [2, 19]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([2, 17], [2, 19]),
                                name: VariableName::from('foo')
                            )
                        )
                    ),
                    new MatchArmNode(
                        rangeInSource: $this->range([3, 4], [3, 18]),
                        left: new ExpressionNodes(
                            new ExpressionNode(
                                rangeInSource: $this->range([3, 4], [3, 7]),
                                root: new NullLiteralNode(
                                    rangeInSource: $this->range([3, 4], [3, 7]),
                                )
                            ),
                        ),
                        right: new ExpressionNode(
                            rangeInSource: $this->range([3, 12], [3, 18]),
                            root: new AccessNode(
                                rangeInSource: $this->range([3, 12], [3, 18]),
                                parent: new ExpressionNode(
                                    rangeInSource: $this->range([3, 12], [3, 14]),
                                    root: new ValueReferenceNode(
                                        rangeInSource: $this->range([3, 12], [3, 14]),
                                        name: VariableName::from('foo')
                                    )
                                ),
                                type: AccessType::MANDATORY,
                                key: new AccessKeyNode(
                                    rangeInSource: $this->range([3, 16], [3, 18]),
                                    value: PropertyName::from('bar')
                                )
                            )
                        )
                    ),
                    new MatchArmNode(
                        rangeInSource: $this->range([4, 4], [4, 19]),
                        left: null,
                        right: new ExpressionNode(
                            rangeInSource: $this->range([4, 15], [4, 19]),
                            root: new StringLiteralNode(
                                rangeInSource: $this->range([4, 15], [4, 19]),
                                value: 'N/A'
                            )
                        )
                    ),
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesNullLiteral(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('null');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 3]),
            root: new NullLiteralNode(
                rangeInSource: $this->range([0, 0], [0, 3])
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesStringLiteral(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('"Hello World"');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 12]),
            root: new StringLiteralNode(
                rangeInSource: $this->range([0, 0], [0, 12]),
                value: 'Hello World'
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTag(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('<a href="#foo">Bar!</a>');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 22]),
            root: new TagNode(
                rangeInSource: $this->range([0, 0], [0, 22]),
                name: new TagNameNode(
                    rangeInSource: $this->range([0, 1], [0, 1]),
                    value: TagName::from('a')
                ),
                attributes: new AttributeNodes(
                    new AttributeNode(
                        rangeInSource: $this->range([0, 3], [0, 13]),
                        name: new AttributeNameNode(
                            rangeInSource: $this->range([0, 3], [0, 6]),
                            value: AttributeName::from('href')
                        ),
                        value: new StringLiteralNode(
                            rangeInSource: $this->range([0, 8], [0, 13]),
                            value: '#foo'
                        )
                    )
                ),
                children: new ChildNodes(
                    new TextNode(
                        rangeInSource: $this->range([0, 15], [0, 18]),
                        value: 'Bar!'
                    )
                ),
                isSelfClosing: false
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTemplateLiteral(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer(<<<EOF
        """
        Hello {friend}!
        """
        EOF);

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [2, 2]),
            root: new TemplateLiteralNode(
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
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTernaryOperation(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('a ? b : c');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 8]),
            root: new TernaryOperationNode(
                condition: new ExpressionNode(
                    rangeInSource: $this->range([0, 0], [0, 0]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 0], [0, 0]),
                        name: VariableName::from('a')
                    )
                ),
                trueBranch: new ExpressionNode(
                    rangeInSource: $this->range([0, 4], [0, 4]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 4], [0, 4]),
                        name: VariableName::from('b')
                    )
                ),
                falseBranch: new ExpressionNode(
                    rangeInSource: $this->range([0, 8], [0, 8]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 8], [0, 8]),
                        name: VariableName::from('c')
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesNestedBracketedTernaryOperation(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('(a ? b : c) ? (d ? e : f) : (g ? h : i)');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 38]),
            root: new TernaryOperationNode(
                condition: new ExpressionNode(
                    rangeInSource: $this->range([0, 0], [0, 10]),
                    root: new TernaryOperationNode(
                        condition: new ExpressionNode(
                            rangeInSource: $this->range([0, 1], [0, 1]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 1], [0, 1]),
                                name: VariableName::from('a')
                            )
                        ),
                        trueBranch: new ExpressionNode(
                            rangeInSource: $this->range([0, 5], [0, 5]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 5], [0, 5]),
                                name: VariableName::from('b')
                            )
                        ),
                        falseBranch: new ExpressionNode(
                            rangeInSource: $this->range([0, 9], [0, 9]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 9], [0, 9]),
                                name: VariableName::from('c')
                            )
                        )
                    )
                ),
                trueBranch: new ExpressionNode(
                    rangeInSource: $this->range([0, 14], [0, 24]),
                    root: new TernaryOperationNode(
                        condition: new ExpressionNode(
                            rangeInSource: $this->range([0, 15], [0, 15]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 15], [0, 15]),
                                name: VariableName::from('d')
                            )
                        ),
                        trueBranch: new ExpressionNode(
                            rangeInSource: $this->range([0, 19], [0, 19]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 19], [0, 19]),
                                name: VariableName::from('e')
                            )
                        ),
                        falseBranch: new ExpressionNode(
                            rangeInSource: $this->range([0, 23], [0, 23]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 23], [0, 23]),
                                name: VariableName::from('f')
                            )
                        )
                    )
                ),
                falseBranch: new ExpressionNode(
                    rangeInSource: $this->range([0, 28], [0, 38]),
                    root: new TernaryOperationNode(
                        condition: new ExpressionNode(
                            rangeInSource: $this->range([0, 29], [0, 29]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 29], [0, 29]),
                                name: VariableName::from('g')
                            )
                        ),
                        trueBranch: new ExpressionNode(
                            rangeInSource: $this->range([0, 33], [0, 33]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 33], [0, 33]),
                                name: VariableName::from('h')
                            )
                        ),
                        falseBranch: new ExpressionNode(
                            rangeInSource: $this->range([0, 37], [0, 37]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 37], [0, 37]),
                                name: VariableName::from('i')
                            )
                        )
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesNestedUnbracketedTernaryOperation(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('a < b ? "yes" : (foo ? "maybe" : "no")');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 37]),
            root: new TernaryOperationNode(
                condition: new ExpressionNode(
                    rangeInSource: $this->range([0, 0], [0, 4]),
                    root: new BinaryOperationNode(
                        rangeInSource: $this->range([0, 0], [0, 4]),
                        leftOperand: new ExpressionNode(
                            rangeInSource: $this->range([0, 0], [0, 0]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 0], [0, 0]),
                                name: VariableName::from('a')
                            )
                        ),
                        operator: BinaryOperator::LESS_THAN,
                        rightOperand: new ExpressionNode(
                            rangeInSource: $this->range([0, 4], [0, 4]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 4], [0, 4]),
                                name: VariableName::from('b')
                            )
                        ),
                    )
                ),
                trueBranch: new ExpressionNode(
                    rangeInSource: $this->range([0, 8], [0, 12]),
                    root: new StringLiteralNode(
                        rangeInSource: $this->range([0, 8], [0, 12]),
                        value: 'yes'
                    )
                ),
                falseBranch: new ExpressionNode(
                    rangeInSource: $this->range([0, 16], [0, 37]),
                    root: new TernaryOperationNode(
                        condition: new ExpressionNode(
                            rangeInSource: $this->range([0, 17], [0, 19]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 17], [0, 19]),
                                name: VariableName::from('foo')
                            ),
                        ),
                        trueBranch: new ExpressionNode(
                            rangeInSource: $this->range([0, 23], [0, 29]),
                            root: new StringLiteralNode(
                                rangeInSource: $this->range([0, 23], [0, 29]),
                                value: 'maybe'
                            )
                        ),
                        falseBranch: new ExpressionNode(
                            rangeInSource: $this->range([0, 33], [0, 36]),
                            root: new StringLiteralNode(
                                rangeInSource: $this->range([0, 33], [0, 36]),
                                value: 'no'
                            )
                        )
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTernaryOperationWithComplexUnbracketedCondition(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer(
            '1 < 2 === a || 5 > b || c === true && false ? "a" : "foo"'
        );

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 56]),
            root: new TernaryOperationNode(
                condition: new ExpressionNode(
                    rangeInSource: $this->range([0, 0], [0, 42]),
                    root: new BinaryOperationNode(
                        rangeInSource: $this->range([0, 0], [0, 42]),
                        operator: BinaryOperator::OR,
                        leftOperand: new ExpressionNode(
                            rangeInSource: $this->range([0, 0], [0, 19]),
                            root: new BinaryOperationNode(
                                rangeInSource: $this->range([0, 0], [0, 19]),
                                operator: BinaryOperator::OR,
                                leftOperand: new ExpressionNode(
                                    rangeInSource: $this->range([0, 0], [0, 10]),
                                    root: new BinaryOperationNode(
                                        rangeInSource: $this->range([0, 0], [0, 10]),
                                        operator: BinaryOperator::EQUAL,
                                        leftOperand: new ExpressionNode(
                                            rangeInSource: $this->range([0, 0], [0, 4]),
                                            root: new BinaryOperationNode(
                                                rangeInSource: $this->range([0, 0], [0, 4]),
                                                operator: BinaryOperator::LESS_THAN,
                                                leftOperand: new ExpressionNode(
                                                    rangeInSource: $this->range([0, 0], [0, 0]),
                                                    root: new IntegerLiteralNode(
                                                        rangeInSource: $this->range([0, 0], [0, 0]),
                                                        format: IntegerFormat::DECIMAL,
                                                        value: '1'
                                                    )
                                                ),
                                                rightOperand: new ExpressionNode(
                                                    rangeInSource: $this->range([0, 4], [0, 4]),
                                                    root: new IntegerLiteralNode(
                                                        rangeInSource: $this->range([0, 4], [0, 4]),
                                                        format: IntegerFormat::DECIMAL,
                                                        value: '2'
                                                    )
                                                ),
                                            )
                                        ),
                                        rightOperand: new ExpressionNode(
                                            rangeInSource: $this->range([0, 10], [0, 10]),
                                            root: new ValueReferenceNode(
                                                rangeInSource: $this->range([0, 10], [0, 10]),
                                                name: VariableName::from('a')
                                            )
                                        )
                                    )
                                ),
                                rightOperand: new ExpressionNode(
                                    rangeInSource: $this->range([0, 15], [0, 19]),
                                    root: new BinaryOperationNode(
                                        rangeInSource: $this->range([0, 15], [0, 19]),
                                        operator: BinaryOperator::GREATER_THAN,
                                        leftOperand: new ExpressionNode(
                                            rangeInSource: $this->range([0, 15], [0, 15]),
                                            root: new IntegerLiteralNode(
                                                rangeInSource: $this->range([0, 15], [0, 15]),
                                                format: IntegerFormat::DECIMAL,
                                                value: '5'
                                            )
                                        ),
                                        rightOperand: new ExpressionNode(
                                            rangeInSource: $this->range([0, 19], [0, 19]),
                                            root: new ValueReferenceNode(
                                                rangeInSource: $this->range([0, 19], [0, 19]),
                                                name: VariableName::from('b')
                                            )
                                        )
                                    )
                                )
                            ),
                        ),
                        rightOperand: new ExpressionNode(
                            rangeInSource: $this->range([0, 24], [0, 42]),
                            root: new BinaryOperationNode(
                                rangeInSource: $this->range([0, 24], [0, 42]),
                                operator: BinaryOperator::AND,
                                leftOperand: new ExpressionNode(
                                    rangeInSource: $this->range([0, 24], [0, 33]),
                                    root: new BinaryOperationNode(
                                        rangeInSource: $this->range([0, 24], [0, 33]),
                                        operator: BinaryOperator::EQUAL,
                                        leftOperand: new ExpressionNode(
                                            rangeInSource: $this->range([0, 24], [0, 24]),
                                            root: new ValueReferenceNode(
                                                rangeInSource: $this->range([0, 24], [0, 24]),
                                                name: VariableName::from('c')
                                            )
                                        ),
                                        rightOperand: new ExpressionNode(
                                            rangeInSource: $this->range([0, 30], [0, 33]),
                                            root: new BooleanLiteralNode(
                                                rangeInSource: $this->range([0, 30], [0, 33]),
                                                value: true
                                            )
                                        )
                                    )
                                ),
                                rightOperand: new ExpressionNode(
                                    rangeInSource: $this->range([0, 38], [0, 42]),
                                    root: new BooleanLiteralNode(
                                        rangeInSource: $this->range([0, 38], [0, 42]),
                                        value: false
                                    )
                                )
                            )
                        )
                    )
                ),
                trueBranch: new ExpressionNode(
                    rangeInSource: $this->range([0, 46], [0, 48]),
                    root: new StringLiteralNode(
                        rangeInSource: $this->range([0, 46], [0, 48]),
                        value: 'a'
                    )
                ),
                falseBranch: new ExpressionNode(
                    rangeInSource: $this->range([0, 52], [0, 56]),
                    root: new StringLiteralNode(
                        rangeInSource: $this->range([0, 52], [0, 56]),
                        value: 'foo'
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTernaryOperationWithComplexParentheses(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('(((foo)) === ((null))) ? 1 : (((0)))');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 35]),
            root: new TernaryOperationNode(
                condition: new ExpressionNode(
                    rangeInSource: $this->range([0, 0], [0, 21]),
                    root: new BinaryOperationNode(
                        rangeInSource: $this->range([0, 1], [0, 20]),
                        leftOperand: new ExpressionNode(
                            rangeInSource: $this->range([0, 1], [0, 7]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 3], [0, 5]),
                                name: VariableName::from('foo')
                            )
                        ),
                        operator: BinaryOperator::EQUAL,
                        rightOperand: new ExpressionNode(
                            rangeInSource: $this->range([0, 13], [0, 20]),
                            root: new NullLiteralNode(
                                rangeInSource: $this->range([0, 15], [0, 18])
                            )
                        ),
                    )
                ),
                trueBranch: new ExpressionNode(
                    rangeInSource: $this->range([0, 25], [0, 25]),
                    root: new IntegerLiteralNode(
                        rangeInSource: $this->range([0, 25], [0, 25]),
                        format: IntegerFormat::DECIMAL,
                        value: '1'
                    )
                ),
                falseBranch: new ExpressionNode(
                    rangeInSource: $this->range([0, 29], [0, 35]),
                    root: new IntegerLiteralNode(
                        rangeInSource: $this->range([0, 32], [0, 32]),
                        format: IntegerFormat::DECIMAL,
                        value: '0'
                    ),
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesUnaryOperation(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('!a');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 1]),
            root: new UnaryOperationNode(
                rangeInSource: $this->range([0, 0], [0, 1]),
                operator: UnaryOperator::NOT,
                operand: new ExpressionNode(
                    rangeInSource: $this->range([0, 1], [0, 1]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 1], [0, 1]),
                        name: VariableName::from('a')
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesDoubleUnaryOperation(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('!!a');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 2]),
            root: new UnaryOperationNode(
                rangeInSource: $this->range([0, 0], [0, 2]),
                operator: UnaryOperator::NOT,
                operand: new ExpressionNode(
                    rangeInSource: $this->range([0, 1], [0, 2]),
                    root: new UnaryOperationNode(
                        rangeInSource: $this->range([0, 1], [0, 2]),
                        operator: UnaryOperator::NOT,
                        operand: new ExpressionNode(
                            rangeInSource: $this->range([0, 2], [0, 2]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 2], [0, 2]),
                                name: VariableName::from('a')
                            )
                        )
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesTripleUnaryOperation(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('!!!a');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 3]),
            root: new UnaryOperationNode(
                rangeInSource: $this->range([0, 0], [0, 3]),
                operator: UnaryOperator::NOT,
                operand: new ExpressionNode(
                    rangeInSource: $this->range([0, 1], [0, 3]),
                    root: new UnaryOperationNode(
                        rangeInSource: $this->range([0, 1], [0, 3]),
                        operator: UnaryOperator::NOT,
                        operand: new ExpressionNode(
                            rangeInSource: $this->range([0, 2], [0, 3]),
                            root: new UnaryOperationNode(
                                rangeInSource: $this->range([0, 2], [0, 3]),
                                operator: UnaryOperator::NOT,
                                operand: new ExpressionNode(
                                    rangeInSource: $this->range([0, 3], [0, 3]),
                                    root: new ValueReferenceNode(
                                        rangeInSource: $this->range([0, 3], [0, 3]),
                                        name: VariableName::from('a')
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesUnaryOperationWithBracketedExpressionAsOperand(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('!(a > b)');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 7]),
            root: new UnaryOperationNode(
                rangeInSource: $this->range([0, 0], [0, 7]),
                operator: UnaryOperator::NOT,
                operand: new ExpressionNode(
                    rangeInSource: $this->range([0, 1], [0, 7]),
                    root: new BinaryOperationNode(
                        rangeInSource: $this->range([0, 2], [0, 6]),
                        leftOperand: new ExpressionNode(
                            rangeInSource: $this->range([0, 2], [0, 2]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 2], [0, 2]),
                                name: VariableName::from('a')
                            )
                        ),
                        operator: BinaryOperator::GREATER_THAN,
                        rightOperand: new ExpressionNode(
                            rangeInSource: $this->range([0, 6], [0, 6]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 6], [0, 6]),
                                name: VariableName::from('b')
                            )
                        )
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesValueReference(): void
    {
        $expressionParser = new ExpressionParser();
        $lexer = new Lexer('foo');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 2]),
            root: new ValueReferenceNode(
                rangeInSource: $this->range([0, 0], [0, 2]),
                name: VariableName::from('foo')
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesMultipleParenthesesAroundValureReferenceCorrecly(): void
    {
        $expressionParser = new ExpressionParser();

        $lexer = new Lexer('(foo)');
        $this->assertEquals(
            new ExpressionNode(
                rangeInSource: $this->range([0, 0], [0, 4]),
                root: new ValueReferenceNode(
                    rangeInSource: $this->range([0, 1], [0, 3]),
                    name: VariableName::from('foo')
                )
            ),
            $expressionParser->parse($lexer)
        );

        $lexer = new Lexer('((foo))');
        $this->assertEquals(
            new ExpressionNode(
                rangeInSource: $this->range([0, 0], [0, 6]),
                root: new ValueReferenceNode(
                    rangeInSource: $this->range([0, 2], [0, 4]),
                    name: VariableName::from('foo')
                )
            ),
            $expressionParser->parse($lexer)
        );

        $lexer = new Lexer('(((foo)))');
        $this->assertEquals(
            new ExpressionNode(
                rangeInSource: $this->range([0, 0], [0, 8]),
                root: new ValueReferenceNode(
                    rangeInSource: $this->range([0, 3], [0, 5]),
                    name: VariableName::from('foo')
                )
            ),
            $expressionParser->parse($lexer)
        );
    }
}
