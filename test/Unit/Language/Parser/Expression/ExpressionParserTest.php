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
use PackageFactory\ComponentEngine\Language\AST\Node\IntegerLiteral\IntegerFormat;
use PackageFactory\ComponentEngine\Language\AST\Node\IntegerLiteral\IntegerLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\NullLiteral\NullLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StringLiteral\StringLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\AttributeNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\AttributeNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\AttributeNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\ChildNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\TagNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\TagNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TernaryOperation\TernaryOperationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Text\TextNode;
use PackageFactory\ComponentEngine\Language\AST\Node\UnaryOperation\UnaryOperationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\UnaryOperation\UnaryOperator;
use PackageFactory\ComponentEngine\Language\AST\Node\ValueReference\ValueReferenceNode;
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
        $tokens = $this->createTokenIterator('a.b');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesMandatoryAccessWithMultipleLevels(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('a.b.c.d.e');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesOptionalAccessWithOneLevel(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('a?.b');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesOptionalAccessWithMultipleLevels(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('a?.b?.c?.d?.e');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesMixedAccessChainStartingWithMandatoryAccess(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('a.b?.c');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesMixedAccessChainStartingWithOptionalAccess(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('a?.b.c');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesMandatoryAccessWithBracketedEpxressionAsParent(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('(a ? b : c).d');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesOptionalAccessWithBracketedEpxressionAsParent(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('(a ? b : c)?.d');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesBinaryOperationAnd(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('a && b');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesBinaryOperationOr(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('a || b');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesBinaryOperationEquals(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('a === b');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesBinaryOperationNotEquals(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('a !== b');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesBinaryOperationGreaterThan(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('a > b');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesBinaryOperationGreaterThanOrEqual(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('a >= b');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesBinaryOperationLessThan(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('a < b');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesBinaryOperationLessThanOrEqual(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('a <= b');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesBinaryOperationInBrackets(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('(a <= b)');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesBinaryOperationInMultipleBrackets(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('((((a <= b))))');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesBooleanLiteralTrue(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('true');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 3]),
            root: new BooleanLiteralNode(
                rangeInSource: $this->range([0, 0], [0, 3]),
                value: true
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesBooleanLiteralFalse(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('false');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 4]),
            root: new BooleanLiteralNode(
                rangeInSource: $this->range([0, 0], [0, 4]),
                value: false
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesBinaryIntegerLiteral(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('0b1001');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesOctalIntegerLiteral(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('0o755');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesDecimalIntegerLiteral(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('42');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesHexadecimalIntegerLiteral(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('0xABC');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesMatch(): void
    {
        $this->markTestSkipped('@TODO: parses Match');
    }

    /**
     * @test
     */
    public function parsesNullLiteral(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('null');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 3]),
            root: new NullLiteralNode(
                rangeInSource: $this->range([0, 0], [0, 3])
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesStringLiteral(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('"Hello World"');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 1], [0, 11]),
            root: new StringLiteralNode(
                rangeInSource: $this->range([0, 1], [0, 11]),
                value: 'Hello World'
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesTag(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('<a href="#foo">Bar!</a>');

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
                        rangeInSource: $this->range([0, 3], [0, 12]),
                        name: new AttributeNameNode(
                            rangeInSource: $this->range([0, 3], [0, 6]),
                            value: AttributeName::from('href')
                        ),
                        value: new StringLiteralNode(
                            rangeInSource: $this->range([0, 9], [0, 12]),
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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesTemplateLiteral(): void
    {
        $this->markTestSkipped('@TODO: parses TemplateLiteral');
    }

    /**
     * @test
     */
    public function parsesTernaryOperation(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('a ? b : c');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesNestedTernaryOperation(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('(a ? b : c) ? (d ? e : f) : (g ? h : i)');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesUnaryOperation(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('!a');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesDoubleUnaryOperation(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('!!a');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesTripleUnaryOperation(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('!!!a');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesUnaryOperationWithBracketedExpressionAsOperand(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('!(a > b)');

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
            $expressionParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesValueReference(): void
    {
        $expressionParser = new ExpressionParser();
        $tokens = $this->createTokenIterator('foo');

        $expectedExpressioNode = new ExpressionNode(
            rangeInSource: $this->range([0, 0], [0, 2]),
            root: new ValueReferenceNode(
                rangeInSource: $this->range([0, 0], [0, 2]),
                name: VariableName::from('foo')
            )
        );

        $this->assertEquals(
            $expectedExpressioNode,
            $expressionParser->parse($tokens)
        );
    }
}
