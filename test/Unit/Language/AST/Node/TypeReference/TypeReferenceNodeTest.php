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
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\InvalidTypeReferenceNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeReferenceNode;
use PackageFactory\ComponentEngine\Language\AST\NodeAttributes\NodeAttributes;
use PackageFactory\ComponentEngine\Parser\Source\Path;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PHPUnit\Framework\TestCase;

final class TypeReferenceNodeTest extends TestCase
{
    private NodeAttributes $dummyAttributes;

    public function setUp(): void
    {
        $this->dummyAttributes = new NodeAttributes(
            pathToSource: Path::fromString(':memory:'),
            rangeInSource: Range::from(
                new Position(0, 0),
                new Position(0, 0)
            )
        );
    }

    /**
     * @test
     */
    public function validSimpleTypeReferenceIsValid(): void
    {
        $typeReferenceNode = new TypeReferenceNode(
            attributes: $this->dummyAttributes,
            name: new TypeNameNode(
                attributes: $this->dummyAttributes,
                value: TypeName::from('Foo')
            ),
            isArray: false,
            isOptional: false
        );

        $this->assertEquals('Foo', $typeReferenceNode->name->value);
        $this->assertFalse($typeReferenceNode->isArray);
        $this->assertFalse($typeReferenceNode->isOptional);
    }

    /**
     * @test
     */
    public function validArrayTypeReferenceIsValid(): void
    {
        $typeReferenceNode = new TypeReferenceNode(
            attributes: $this->dummyAttributes,
            name: new TypeNameNode(
                attributes: $this->dummyAttributes,
                value: TypeName::from('Foo')
            ),
            isArray: true,
            isOptional: false
        );

        $this->assertEquals('Foo', $typeReferenceNode->name->value);
        $this->assertTrue($typeReferenceNode->isArray);
        $this->assertFalse($typeReferenceNode->isOptional);
    }

    /**
     * @test
     */
    public function validOptionalTypeReferenceIsValid(): void
    {
        $typeReferenceNode = new TypeReferenceNode(
            attributes: $this->dummyAttributes,
            name: new TypeNameNode(
                attributes: $this->dummyAttributes,
                value: TypeName::from('Foo')
            ),
            isArray: false,
            isOptional: true
        );

        $this->assertEquals('Foo', $typeReferenceNode->name->value);
        $this->assertFalse($typeReferenceNode->isArray);
        $this->assertTrue($typeReferenceNode->isOptional);
    }

    /**
     * @test
     */
    public function typeReferenceCannotBeArrayAndOptionalSimultaneously(): void
    {
        $name = TypeName::from('Foo');

        $this->expectExceptionObject(
            InvalidTypeReferenceNode::becauseItWasOptionalAndArrayAtTheSameTime(
                affectedTypeName: $name,
                attributesOfAffectedNode: $this->dummyAttributes
            )
        );

        new TypeReferenceNode(
            attributes: $this->dummyAttributes,
            name: new TypeNameNode(
                attributes: $this->dummyAttributes,
                value: $name
            ),
            isArray: true,
            isOptional: true
        );
    }
}
