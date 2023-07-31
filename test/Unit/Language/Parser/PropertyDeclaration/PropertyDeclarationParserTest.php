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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Parser\PropertyDeclaration;

use PackageFactory\ComponentEngine\Domain\PropertyName\PropertyName;
use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\Language\AST\Node\PropertyDeclaration\PropertyDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\PropertyDeclaration\PropertyNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeNameNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeReferenceNode;
use PackageFactory\ComponentEngine\Language\Parser\PropertyDeclaration\PropertyDeclarationParser;
use PackageFactory\ComponentEngine\Language\AST\NodeAttributes\NodeAttributes;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Tokenizer;
use PHPUnit\Framework\TestCase;

final class PropertyDeclarationParserTest extends TestCase
{
    /**
     * @test
     */
    public function parsesPropertyDeclarationWithSimpleType(): void
    {
        $propertyDeclarationParser = new PropertyDeclarationParser();
        $tokens = Tokenizer::fromSource(Source::fromString('foo: Bar'))->getIterator();

        $expectedPropertyDeclarationNode = new PropertyDeclarationNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 7)
                )
            ),
            name: new PropertyNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 0),
                        new Position(0, 2)
                    )
                ),
                value: PropertyName::from('foo')
            ),
            type: new TypeReferenceNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 5),
                        new Position(0, 7)
                    )
                ),
                names: new TypeNameNodes(
                    new TypeNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 5),
                                new Position(0, 7)
                            )
                        ),
                        value: TypeName::from('Bar')
                    )
                ),
                isArray: false,
                isOptional: false
            )
        );

        $this->assertEquals(
            $expectedPropertyDeclarationNode,
            $propertyDeclarationParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesPropertyDeclarationWithOptionalType(): void
    {
        $propertyDeclarationParser = new PropertyDeclarationParser();
        $tokens = Tokenizer::fromSource(Source::fromString('foo: ?Bar'))->getIterator();

        $expectedPropertyDeclarationNode = new PropertyDeclarationNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 8)
                )
            ),
            name: new PropertyNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 0),
                        new Position(0, 2)
                    )
                ),
                value: PropertyName::from('foo')
            ),
            type: new TypeReferenceNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 5),
                        new Position(0, 8)
                    )
                ),
                names: new TypeNameNodes(
                    new TypeNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 6),
                                new Position(0, 8)
                            )
                        ),
                        value: TypeName::from('Bar')
                    )
                ),
                isArray: false,
                isOptional: true
            )
        );

        $this->assertEquals(
            $expectedPropertyDeclarationNode,
            $propertyDeclarationParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesPropertyDeclarationWithArrayType(): void
    {
        $propertyDeclarationParser = new PropertyDeclarationParser();
        $tokens = Tokenizer::fromSource(Source::fromString('foo: Bar[]'))->getIterator();

        $expectedPropertyDeclarationNode = new PropertyDeclarationNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 9)
                )
            ),
            name: new PropertyNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 0),
                        new Position(0, 2)
                    )
                ),
                value: PropertyName::from('foo')
            ),
            type: new TypeReferenceNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 5),
                        new Position(0, 9)
                    )
                ),
                names: new TypeNameNodes(
                    new TypeNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 5),
                                new Position(0, 7)
                            )
                        ),
                        value: TypeName::from('Bar')
                    )
                ),
                isArray: true,
                isOptional: false
            )
        );

        $this->assertEquals(
            $expectedPropertyDeclarationNode,
            $propertyDeclarationParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesPropertyDeclarationWithUnionType(): void
    {
        $propertyDeclarationParser = new PropertyDeclarationParser();
        $tokens = Tokenizer::fromSource(Source::fromString('foo: Bar|Baz|Qux'))->getIterator();

        $expectedPropertyDeclarationNode = new PropertyDeclarationNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 15)
                )
            ),
            name: new PropertyNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 0),
                        new Position(0, 2)
                    )
                ),
                value: PropertyName::from('foo')
            ),
            type: new TypeReferenceNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 5),
                        new Position(0, 15)
                    )
                ),
                names: new TypeNameNodes(
                    new TypeNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 5),
                                new Position(0, 7)
                            )
                        ),
                        value: TypeName::from('Bar')
                    ),
                    new TypeNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 9),
                                new Position(0, 11)
                            )
                        ),
                        value: TypeName::from('Baz')
                    ),
                    new TypeNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 13),
                                new Position(0, 15)
                            )
                        ),
                        value: TypeName::from('Qux')
                    )
                ),
                isArray: false,
                isOptional: false
            )
        );

        $this->assertEquals(
            $expectedPropertyDeclarationNode,
            $propertyDeclarationParser->parse($tokens)
        );
    }
}
