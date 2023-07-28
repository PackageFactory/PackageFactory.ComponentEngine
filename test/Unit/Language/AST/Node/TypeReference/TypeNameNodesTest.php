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
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\InvalidTypeNameNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeNameNodes;
use PackageFactory\ComponentEngine\Language\AST\NodeAttributes\NodeAttributes;
use PackageFactory\ComponentEngine\Parser\Source\Path;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PackageFactory\ComponentEngine\Test\Unit\Language\AST\Helpers\DummyAttributes;
use PHPUnit\Framework\TestCase;

final class TypeNameNodesTest extends TestCase
{
    use DummyAttributes;

    protected function createTypeNameNode(string $typeName): TypeNameNode
    {
        return new TypeNameNode(
            attributes: $this->dummyAttributes,
            value: TypeName::from($typeName)
        );
    }

    /**
     * @test
     */
    public function mustNotBeEmpty(): void
    {
        $this->expectExceptionObject(
            InvalidTypeNameNodes::becauseTheyWereEmpty()
        );

        new TypeNameNodes();
    }

    /**
     * @test
     */
    public function mustNotContainDuplicates(): void
    {
        $duplicate = new TypeNameNode(
            attributes: new NodeAttributes(
                pathToSource: Path::fromString(':memory:'),
                rangeInSource: Range::from(
                    new Position(1, 1),
                    new Position(1, 1)
                )
            ),
            value: TypeName::from('Foo')
        );

        $this->expectExceptionObject(
            InvalidTypeNameNodes::becauseTheyContainDuplicates(
                duplicateTypeNameNode: $duplicate
            )
        );

        new TypeNameNodes(
            $this->createTypeNameNode('Foo'),
            $this->createTypeNameNode('Bar'),
            $duplicate,
            $this->createTypeNameNode('Baz'),
        );
    }

    /**
     * @test
     */
    public function providesItsOwnSize(): void
    {
        $typeNameNodes = new TypeNameNodes(
            $this->createTypeNameNode('Foo')
        );

        $this->assertEquals(1, $typeNameNodes->getSize());

        $typeNameNodes = new TypeNameNodes(
            $this->createTypeNameNode('Foo'),
            $this->createTypeNameNode('Bar')
        );

        $this->assertEquals(2, $typeNameNodes->getSize());

        $typeNameNodes = new TypeNameNodes(
            $this->createTypeNameNode('Foo'),
            $this->createTypeNameNode('Bar'),
            $this->createTypeNameNode('Baz')
        );

        $this->assertEquals(3, $typeNameNodes->getSize());
    }

    /**
     * @test
     */
    public function convertsToTypeNames(): void
    {
        $typeNameNodes = new TypeNameNodes(
            $this->createTypeNameNode('Foo'),
            $this->createTypeNameNode('Bar'),
            $this->createTypeNameNode('Baz')
        );

        $this->assertEquals(
            new TypeNames(
                TypeName::from('Foo'),
                TypeName::from('Bar'),
                TypeName::from('Baz')
            ),
            $typeNameNodes->toTypeNames()
        );
    }
}
