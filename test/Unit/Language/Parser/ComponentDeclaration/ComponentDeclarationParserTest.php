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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Parser\ComponentDeclaration;

use PackageFactory\ComponentEngine\Domain\AttributeName\AttributeName;
use PackageFactory\ComponentEngine\Domain\ComponentName\ComponentName;
use PackageFactory\ComponentEngine\Domain\PropertyName\PropertyName;
use PackageFactory\ComponentEngine\Domain\TagName\TagName;
use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\Domain\VariableName\VariableName;
use PackageFactory\ComponentEngine\Language\AST\Node\ComponentDeclaration\ComponentDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\ComponentDeclaration\ComponentNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Expression\ExpressionNode;
use PackageFactory\ComponentEngine\Language\AST\Node\PropertyDeclaration\PropertyDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\PropertyDeclaration\PropertyDeclarationNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\PropertyDeclaration\PropertyNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StringLiteral\StringLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\AttributeNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\AttributeNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\AttributeNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\ChildNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\TagNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\TagNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeNameNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeReferenceNode;
use PackageFactory\ComponentEngine\Language\AST\Node\ValueReference\ValueReferenceNode;
use PackageFactory\ComponentEngine\Language\Parser\ComponentDeclaration\ComponentDeclarationParser;
use PackageFactory\ComponentEngine\Test\Unit\Language\Parser\ParserTestCase;

final class ComponentDeclarationParserTest extends ParserTestCase
{
    /**
     * @test
     */
    public function parsesComponentDeclarationWithNoProps(): void
    {
        $componentDeclarationParser = new ComponentDeclarationParser();
        $tokens = $this->createTokenIterator('component Foo { return "bar" }');

        $expectedComponentDeclarationNode = new ComponentDeclarationNode(
            rangeInSource: $this->range([0, 0], [0, 29]),
            name: new ComponentNameNode(
                rangeInSource: $this->range([0, 10], [0, 12]),
                value: ComponentName::from('Foo')
            ),
            props: new PropertyDeclarationNodes(),
            return: new ExpressionNode(
                rangeInSource: $this->range([0, 24], [0, 26]),
                root: new StringLiteralNode(
                    rangeInSource: $this->range([0, 24], [0, 26]),
                    value: 'bar'
                )
            )
        );

        $this->assertEquals(
            $expectedComponentDeclarationNode,
            $componentDeclarationParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesComponentDeclarationWithOneProp(): void
    {
        $componentDeclarationParser = new ComponentDeclarationParser();
        $tokens = $this->createTokenIterator('component Foo { bar: string return bar }');

        $expectedComponentDeclarationNode = new ComponentDeclarationNode(
            rangeInSource: $this->range([0, 0], [0, 39]),
            name: new ComponentNameNode(
                rangeInSource: $this->range([0, 10], [0, 12]),
                value: ComponentName::from('Foo')
            ),
            props: new PropertyDeclarationNodes(
                new PropertyDeclarationNode(
                    rangeInSource: $this->range([0, 16], [0, 26]),
                    name: new PropertyNameNode(
                        rangeInSource: $this->range([0, 16], [0, 18]),
                        value: PropertyName::from('bar')
                    ),
                    type: new TypeReferenceNode(
                        rangeInSource: $this->range([0, 21], [0, 26]),
                        names: new TypeNameNodes(
                            new TypeNameNode(
                                rangeInSource: $this->range([0, 21], [0, 26]),
                                value: TypeName::from('string')
                            )
                        ),
                        isArray: false,
                        isOptional: false
                    )
                )
            ),
            return: new ExpressionNode(
                rangeInSource: $this->range([0, 35], [0, 37]),
                root: new ValueReferenceNode(
                    rangeInSource: $this->range([0, 35], [0, 37]),
                    name: VariableName::from('bar')
                )
            )
        );

        $this->assertEquals(
            $expectedComponentDeclarationNode,
            $componentDeclarationParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesComponentDeclarationWithMultiplePropsAndComplexReturnStatement(): void
    {
        $componentDeclarationParser = new ComponentDeclarationParser();
        $componentAsString = <<<AFX
        component Link {
            href: string|Uri
            target: ?string
            rel: string[]
            children: slot

            return <a href={href} target={target} rel={rel}>{children}</a>
        }
        AFX;
        $tokens = $this->createTokenIterator($componentAsString);

        $expectedComponentDeclarationNode = new ComponentDeclarationNode(
            rangeInSource: $this->range([0, 0], [7, 0]),
            name: new ComponentNameNode(
                rangeInSource: $this->range([0, 10], [0, 13]),
                value: ComponentName::from('Link')
            ),
            props: new PropertyDeclarationNodes(
                new PropertyDeclarationNode(
                    rangeInSource: $this->range([1, 4], [1, 19]),
                    name: new PropertyNameNode(
                        rangeInSource: $this->range([1, 4], [1, 7]),
                        value: PropertyName::from('href')
                    ),
                    type: new TypeReferenceNode(
                        rangeInSource: $this->range([1, 10], [1, 19]),
                        names: new TypeNameNodes(
                            new TypeNameNode(
                                rangeInSource: $this->range([1, 10], [1, 15]),
                                value: TypeName::from('string')
                            ),
                            new TypeNameNode(
                                rangeInSource: $this->range([1, 17], [1, 19]),
                                value: TypeName::from('Uri')
                            )
                        ),
                        isArray: false,
                        isOptional: false
                    )
                ),
                new PropertyDeclarationNode(
                    rangeInSource: $this->range([2, 4], [2, 18]),
                    name: new PropertyNameNode(
                        rangeInSource: $this->range([2, 4], [2, 9]),
                        value: PropertyName::from('target')
                    ),
                    type: new TypeReferenceNode(
                        rangeInSource: $this->range([2, 12], [2, 18]),
                        names: new TypeNameNodes(
                            new TypeNameNode(
                                rangeInSource: $this->range([2, 13], [2, 18]),
                                value: TypeName::from('string')
                            )
                        ),
                        isArray: false,
                        isOptional: true
                    )
                ),
                new PropertyDeclarationNode(
                    rangeInSource: $this->range([3, 4], [3, 16]),
                    name: new PropertyNameNode(
                        rangeInSource: $this->range([3, 4], [3, 6]),
                        value: PropertyName::from('rel')
                    ),
                    type: new TypeReferenceNode(
                        rangeInSource: $this->range([3, 9], [3, 16]),
                        names: new TypeNameNodes(
                            new TypeNameNode(
                                rangeInSource: $this->range([3, 9], [3, 14]),
                                value: TypeName::from('string')
                            )
                        ),
                        isArray: true,
                        isOptional: false
                    )
                ),
                new PropertyDeclarationNode(
                    rangeInSource: $this->range([4, 4], [4, 17]),
                    name: new PropertyNameNode(
                        rangeInSource: $this->range([4, 4], [4, 11]),
                        value: PropertyName::from('children')
                    ),
                    type: new TypeReferenceNode(
                        rangeInSource: $this->range([4, 14], [4, 17]),
                        names: new TypeNameNodes(
                            new TypeNameNode(
                                rangeInSource: $this->range([4, 14], [4, 17]),
                                value: TypeName::from('slot')
                            )
                        ),
                        isArray: false,
                        isOptional: false
                    )
                ),
            ),
            return: new ExpressionNode(
                rangeInSource: $this->range([6, 11], [6, 65]),
                root: new TagNode(
                    rangeInSource: $this->range([6, 11], [6, 65]),
                    name: new TagNameNode(
                        rangeInSource: $this->range([6, 12], [6, 12]),
                        value: TagName::from('a')
                    ),
                    attributes: new AttributeNodes(
                        new AttributeNode(
                            rangeInSource: $this->range([6, 14], [6, 23]),
                            name: new AttributeNameNode(
                                rangeInSource: $this->range([6, 14], [6, 17]),
                                value: AttributeName::from('href')
                            ),
                            value: new ExpressionNode(
                                rangeInSource: $this->range([6, 20], [6, 23]),
                                root: new ValueReferenceNode(
                                    rangeInSource: $this->range([6, 20], [6, 23]),
                                    name: VariableName::from('href')
                                )
                            )
                        ),
                        new AttributeNode(
                            rangeInSource: $this->range([6, 26], [6, 39]),
                            name: new AttributeNameNode(
                                rangeInSource: $this->range([6, 26], [6, 31]),
                                value: AttributeName::from('target')
                            ),
                            value: new ExpressionNode(
                                rangeInSource: $this->range([6, 34], [6, 39]),
                                root: new ValueReferenceNode(
                                    rangeInSource: $this->range([6, 34], [6, 39]),
                                    name: VariableName::from('target')
                                )
                            )
                        ),
                        new AttributeNode(
                            rangeInSource: $this->range([6, 42], [6, 49]),
                            name: new AttributeNameNode(
                                rangeInSource: $this->range([6, 42], [6, 44]),
                                value: AttributeName::from('rel')
                            ),
                            value: new ExpressionNode(
                                rangeInSource: $this->range([6, 47], [6, 49]),
                                root: new ValueReferenceNode(
                                    rangeInSource: $this->range([6, 47], [6, 49]),
                                    name: VariableName::from('rel')
                                )
                            )
                        )
                    ),
                    children: new ChildNodes(
                        new ExpressionNode(
                            rangeInSource: $this->range([6, 53], [6, 60]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([6, 53], [6, 60]),
                                name: VariableName::from('children')
                            )
                        )
                    ),
                    isSelfClosing: false
                )
            )
        );

        $this->assertEquals(
            $expectedComponentDeclarationNode,
            $componentDeclarationParser->parse($tokens)
        );
    }
}
