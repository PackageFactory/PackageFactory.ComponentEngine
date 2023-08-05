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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Parser\StructDeclaration;

use PackageFactory\ComponentEngine\Domain\PropertyName\PropertyName;
use PackageFactory\ComponentEngine\Domain\StructName\StructName;
use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\Language\AST\Node\PropertyDeclaration\PropertyDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\PropertyDeclaration\PropertyDeclarationNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\PropertyDeclaration\PropertyNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StructDeclaration\StructDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StructDeclaration\StructNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeNameNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeReferenceNode;
use PackageFactory\ComponentEngine\Language\Parser\StructDeclaration\StructDeclarationParser;
use PackageFactory\ComponentEngine\Test\Unit\Language\Parser\ParserTestCase;

final class StructDeclarationParserTest extends ParserTestCase
{
    /**
     * @test
     */
    public function parsesStructDeclarationWithOneProperty(): void
    {
        $structDeclarationParser = new StructDeclarationParser();
        $tokens = $this->createTokenIterator('struct Foo { bar: Baz }');

        $expectedStructDeclarationNode = new StructDeclarationNode(
            rangeInSource: $this->range([0, 0], [0, 22]),
            name: new StructNameNode(
                rangeInSource: $this->range([0, 7], [0, 9]),
                value: StructName::from('Foo')
            ),
            properties: new PropertyDeclarationNodes(
                new PropertyDeclarationNode(
                    rangeInSource: $this->range([0, 13], [0, 20]),
                    name: new PropertyNameNode(
                        rangeInSource: $this->range([0, 13], [0, 15]),
                        value: PropertyName::from('bar')
                    ),
                    type: new TypeReferenceNode(
                        rangeInSource: $this->range([0, 18], [0, 20]),
                        names: new TypeNameNodes(
                            new TypeNameNode(
                                rangeInSource: $this->range([0, 18], [0, 20]),
                                value: TypeName::from('Baz')
                            )
                        ),
                        isArray: false,
                        isOptional: false
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedStructDeclarationNode,
            $structDeclarationParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesStructDeclarationWithMultipleProperties(): void
    {
        $structDeclarationParser = new StructDeclarationParser();
        $tokens = $this->createTokenIterator('struct Foo { bar: Baz qux: Quux corge: Grault }');

        $expectedStructDeclarationNode = new StructDeclarationNode(
            rangeInSource: $this->range([0, 0], [0, 46]),
            name: new StructNameNode(
                rangeInSource: $this->range([0, 7], [0, 9]),
                value: StructName::from('Foo')
            ),
            properties: new PropertyDeclarationNodes(
                new PropertyDeclarationNode(
                    rangeInSource: $this->range([0, 13], [0, 20]),
                    name: new PropertyNameNode(
                        rangeInSource: $this->range([0, 13], [0, 15]),
                        value: PropertyName::from('bar')
                    ),
                    type: new TypeReferenceNode(
                        rangeInSource: $this->range([0, 18], [0, 20]),
                        names: new TypeNameNodes(
                            new TypeNameNode(
                                rangeInSource: $this->range([0, 18], [0, 20]),
                                value: TypeName::from('Baz')
                            )
                        ),
                        isArray: false,
                        isOptional: false
                    )
                ),
                new PropertyDeclarationNode(
                    rangeInSource: $this->range([0, 22], [0, 30]),
                    name: new PropertyNameNode(
                        rangeInSource: $this->range([0, 22], [0, 24]),
                        value: PropertyName::from('qux')
                    ),
                    type: new TypeReferenceNode(
                        rangeInSource: $this->range([0, 27], [0, 30]),
                        names: new TypeNameNodes(
                            new TypeNameNode(
                                rangeInSource: $this->range([0, 27], [0, 30]),
                                value: TypeName::from('Quux')
                            )
                        ),
                        isArray: false,
                        isOptional: false
                    )
                ),
                new PropertyDeclarationNode(
                    rangeInSource: $this->range([0, 32], [0, 44]),
                    name: new PropertyNameNode(
                        rangeInSource: $this->range([0, 32], [0, 36]),
                        value: PropertyName::from('corge')
                    ),
                    type: new TypeReferenceNode(
                        rangeInSource: $this->range([0, 39], [0, 44]),
                        names: new TypeNameNodes(
                            new TypeNameNode(
                                rangeInSource: $this->range([0, 39], [0, 44]),
                                value: TypeName::from('Grault')
                            )
                        ),
                        isArray: false,
                        isOptional: false
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedStructDeclarationNode,
            $structDeclarationParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesStructDeclarationWithMultiplePropertiesAndSpaceAndComments(): void
    {
        $structDeclarationParser = new StructDeclarationParser();
        $structAsString = <<<AFX
        struct Link {

            # The URI this link leads to
            href: string

            # The frame this link opens in
            target: string

        }
        AFX;
        $tokens = $this->createTokenIterator($structAsString);

        $expectedStructDeclarationNode = new StructDeclarationNode(
            rangeInSource: $this->range([0, 0], [8, 0]),
            name: new StructNameNode(
                rangeInSource: $this->range([0, 7], [0, 10]),
                value: StructName::from('Link')
            ),
            properties: new PropertyDeclarationNodes(
                new PropertyDeclarationNode(
                    rangeInSource: $this->range([3, 4], [3, 15]),
                    name: new PropertyNameNode(
                        rangeInSource: $this->range([3, 4], [3, 7]),
                        value: PropertyName::from('href')
                    ),
                    type: new TypeReferenceNode(
                        rangeInSource: $this->range([3, 10], [3, 15]),
                        names: new TypeNameNodes(
                            new TypeNameNode(
                                rangeInSource: $this->range([3, 10], [3, 15]),
                                value: TypeName::from('string')
                            )
                        ),
                        isArray: false,
                        isOptional: false
                    )
                ),
                new PropertyDeclarationNode(
                    rangeInSource: $this->range([6, 4], [6, 17]),
                    name: new PropertyNameNode(
                        rangeInSource: $this->range([6, 4], [6, 9]),
                        value: PropertyName::from('target')
                    ),
                    type: new TypeReferenceNode(
                        rangeInSource: $this->range([6, 12], [6, 17]),
                        names: new TypeNameNodes(
                            new TypeNameNode(
                                rangeInSource: $this->range([6, 12], [6, 17]),
                                value: TypeName::from('string')
                            )
                        ),
                        isArray: false,
                        isOptional: false
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedStructDeclarationNode,
            $structDeclarationParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesStructDeclarationWitOptionalArrayAndUnionProperties(): void
    {
        $structDeclarationParser = new StructDeclarationParser();
        $structAsString = <<<AFX
        struct Picture {
            src: string[]
            description: string|slot|Description
            title: ?string
        }
        AFX;
        $tokens = $this->createTokenIterator($structAsString);

        $expectedStructDeclarationNode = new StructDeclarationNode(
            rangeInSource: $this->range([0, 0], [4, 0]),
            name: new StructNameNode(
                rangeInSource: $this->range([0, 7], [0, 13]),
                value: StructName::from('Picture')
            ),
            properties: new PropertyDeclarationNodes(
                new PropertyDeclarationNode(
                    rangeInSource: $this->range([1, 4], [1, 16]),
                    name: new PropertyNameNode(
                        rangeInSource: $this->range([1, 4], [1, 6]),
                        value: PropertyName::from('src')
                    ),
                    type: new TypeReferenceNode(
                        rangeInSource: $this->range([1, 9], [1, 16]),
                        names: new TypeNameNodes(
                            new TypeNameNode(
                                rangeInSource: $this->range([1, 9], [1, 14]),
                                value: TypeName::from('string')
                            )
                        ),
                        isArray: true,
                        isOptional: false
                    )
                ),
                new PropertyDeclarationNode(
                    rangeInSource: $this->range([2, 4], [2, 39]),
                    name: new PropertyNameNode(
                        rangeInSource: $this->range([2, 4], [2, 14]),
                        value: PropertyName::from('description')
                    ),
                    type: new TypeReferenceNode(
                        rangeInSource: $this->range([2, 17], [2, 39]),
                        names: new TypeNameNodes(
                            new TypeNameNode(
                                rangeInSource: $this->range([2, 17], [2, 22]),
                                value: TypeName::from('string')
                            ),
                            new TypeNameNode(
                                rangeInSource: $this->range([2, 24], [2, 27]),
                                value: TypeName::from('slot')
                            ),
                            new TypeNameNode(
                                rangeInSource: $this->range([2, 29], [2, 39]),
                                value: TypeName::from('Description')
                            )
                        ),
                        isArray: false,
                        isOptional: false
                    )
                ),
                new PropertyDeclarationNode(
                    rangeInSource: $this->range([3, 4], [3, 17]),
                    name: new PropertyNameNode(
                        rangeInSource: $this->range([3, 4], [3, 8]),
                        value: PropertyName::from('title')
                    ),
                    type: new TypeReferenceNode(
                        rangeInSource: $this->range([3, 11], [3, 17]),
                        names: new TypeNameNodes(
                            new TypeNameNode(
                                rangeInSource: $this->range([3, 12], [3, 17]),
                                value: TypeName::from('string')
                            )
                        ),
                        isArray: false,
                        isOptional: true
                    )
                ),
            )
        );

        $this->assertEquals(
            $expectedStructDeclarationNode,
            $structDeclarationParser->parse($tokens)
        );
    }
}
