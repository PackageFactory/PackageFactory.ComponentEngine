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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\AST\Node\TypeReference;

use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\Domain\TypeName\TypeNames;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\InvalidTypeReferenceNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeNameNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeReferenceNode;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PHPUnit\Framework\TestCase;

final class TypeReferenceNodeTest extends TestCase
{
    /**
     * @test
     */
    public function validSimpleTypeReferenceIsValid(): void
    {
        $typeReferenceNode = new TypeReferenceNode(
            rangeInSource: Range::from(
                new Position(0, 0),
                new Position(0, 0)
            ),
            names: new TypeNameNodes(
                new TypeNameNode(
                    rangeInSource: Range::from(
                        new Position(0, 0),
                        new Position(0, 0)
                    ),
                    value: TypeName::from('Foo')
                )
            ),
            isArray: false,
            isOptional: false
        );

        $this->assertEquals(1, $typeReferenceNode->names->getSize());
        $this->assertEquals('Foo', $typeReferenceNode->names->items[0]->value->value);
        $this->assertFalse($typeReferenceNode->isArray);
        $this->assertFalse($typeReferenceNode->isOptional);
    }

    /**
     * @test
     */
    public function validArrayTypeReferenceIsValid(): void
    {
        $typeReferenceNode = new TypeReferenceNode(
            rangeInSource: Range::from(
                new Position(0, 0),
                new Position(0, 0)
            ),
            names: new TypeNameNodes(
                new TypeNameNode(
                    rangeInSource: Range::from(
                        new Position(0, 0),
                        new Position(0, 0)
                    ),
                    value: TypeName::from('Foo')
                )
            ),
            isArray: true,
            isOptional: false
        );

        $this->assertEquals(1, $typeReferenceNode->names->getSize());
        $this->assertEquals('Foo', $typeReferenceNode->names->items[0]->value->value);
        $this->assertTrue($typeReferenceNode->isArray);
        $this->assertFalse($typeReferenceNode->isOptional);
    }

    /**
     * @test
     */
    public function validOptionalTypeReferenceIsValid(): void
    {
        $typeReferenceNode = new TypeReferenceNode(
            rangeInSource: Range::from(
                new Position(0, 0),
                new Position(0, 0)
            ),
            names: new TypeNameNodes(
                new TypeNameNode(
                    rangeInSource: Range::from(
                        new Position(0, 0),
                        new Position(0, 0)
                    ),
                    value: TypeName::from('Foo')
                )
            ),
            isArray: false,
            isOptional: true
        );

        $this->assertEquals(1, $typeReferenceNode->names->getSize());
        $this->assertEquals('Foo', $typeReferenceNode->names->items[0]->value->value);
        $this->assertFalse($typeReferenceNode->isArray);
        $this->assertTrue($typeReferenceNode->isOptional);
    }

    /**
     * @test
     */
    public function validUnionTypeReferenceIsValid(): void
    {
        $typeReferenceNode = new TypeReferenceNode(
            rangeInSource: Range::from(
                new Position(0, 0),
                new Position(0, 0)
            ),
            names: new TypeNameNodes(
                new TypeNameNode(
                    rangeInSource: Range::from(
                        new Position(0, 0),
                        new Position(0, 0)
                    ),
                    value: TypeName::from('Foo')
                ),
                new TypeNameNode(
                    rangeInSource: Range::from(
                        new Position(0, 0),
                        new Position(0, 0)
                    ),
                    value: TypeName::from('Bar')
                ),
                new TypeNameNode(
                    rangeInSource: Range::from(
                        new Position(0, 0),
                        new Position(0, 0)
                    ),
                    value: TypeName::from('Baz')
                )
            ),
            isArray: false,
            isOptional: false
        );

        $this->assertEquals(3, $typeReferenceNode->names->getSize());
        $this->assertEquals('Foo', $typeReferenceNode->names->items[0]->value->value);
        $this->assertEquals('Bar', $typeReferenceNode->names->items[1]->value->value);
        $this->assertEquals('Baz', $typeReferenceNode->names->items[2]->value->value);
        $this->assertFalse($typeReferenceNode->isArray);
        $this->assertFalse($typeReferenceNode->isOptional);
    }

    /**
     * @test
     */
    public function mustNotBeArrayAndOptionalSimultaneously(): void
    {
        $name = TypeName::from('Foo');

        $this->expectExceptionObject(
            InvalidTypeReferenceNode::becauseItWasOptionalAndArrayAtTheSameTime(
                affectedTypeNames: new TypeNames($name),
                affectedRangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 0)
                )
            )
        );

        new TypeReferenceNode(
            rangeInSource: Range::from(
                new Position(0, 0),
                new Position(0, 0)
            ),
            names: new TypeNameNodes(
                new TypeNameNode(
                    rangeInSource: Range::from(
                        new Position(0, 0),
                        new Position(0, 0)
                    ),
                    value: $name
                )
            ),
            isArray: true,
            isOptional: true
        );
    }

    /**
     * @test
     */
    public function mustNotBeUnionAndArraySimultaneously(): void
    {
        $typeNameNodes = new TypeNameNodes(
            new TypeNameNode(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 0)
                ),
                value: TypeName::from('Foo')
            ),
            new TypeNameNode(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 0)
                ),
                value: TypeName::from('Bar')
            ),
            new TypeNameNode(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 0)
                ),
                value: TypeName::from('Baz')
            )
        );

        $this->expectExceptionObject(
            InvalidTypeReferenceNode::becauseItWasUnionAndArrayAtTheSameTime(
                affectedTypeNames: $typeNameNodes->toTypeNames(),
                affectedRangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 0)
                )
            )
        );

        new TypeReferenceNode(
            rangeInSource: Range::from(
                new Position(0, 0),
                new Position(0, 0)
            ),
            names: $typeNameNodes,
            isArray: true,
            isOptional: false
        );
    }

    /**
     * @test
     */
    public function mustNotBeUnionAndOptionalSimultaneously(): void
    {
        $typeNameNodes = new TypeNameNodes(
            new TypeNameNode(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 0)
                ),
                value: TypeName::from('Foo')
            ),
            new TypeNameNode(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 0)
                ),
                value: TypeName::from('Bar')
            ),
            new TypeNameNode(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 0)
                ),
                value: TypeName::from('Baz')
            )
        );

        $this->expectExceptionObject(
            InvalidTypeReferenceNode::becauseItWasUnionAndOptionalAtTheSameTime(
                affectedTypeNames: $typeNameNodes->toTypeNames(),
                affectedRangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 0)
                )
            )
        );

        new TypeReferenceNode(
            rangeInSource: Range::from(
                new Position(0, 0),
                new Position(0, 0)
            ),
            names: $typeNameNodes,
            isArray: false,
            isOptional: true
        );
    }
}
