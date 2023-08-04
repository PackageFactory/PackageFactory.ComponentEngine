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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\AST\Node\Import;

use PackageFactory\ComponentEngine\Domain\VariableName\VariableName;
use PackageFactory\ComponentEngine\Language\AST\Node\Import\InvalidImportedNameNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Import\ImportedNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Import\ImportedNameNodes;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PHPUnit\Framework\TestCase;

final class ImportedNameNodesTest extends TestCase
{
    protected function createImportedNameNode(string $typeName): ImportedNameNode
    {
        return new ImportedNameNode(
            rangeInSource: Range::from(
                new Position(0, 0),
                new Position(0, 0)
            ),
            value: VariableName::from($typeName)
        );
    }

    /**
     * @test
     */
    public function mustNotBeEmpty(): void
    {
        $this->expectExceptionObject(
            InvalidImportedNameNodes::becauseTheyWereEmpty()
        );

        new ImportedNameNodes();
    }

    /**
     * @test
     */
    public function mustNotContainDuplicates(): void
    {
        $duplicate = new ImportedNameNode(
            rangeInSource: Range::from(
                new Position(1, 1),
                new Position(1, 1)
            ),
            value: VariableName::from('Foo')
        );

        $this->expectExceptionObject(
            InvalidImportedNameNodes::becauseTheyContainDuplicates(
                duplicateImportedNameNode: $duplicate
            )
        );

        new ImportedNameNodes(
            $this->createImportedNameNode('Foo'),
            $this->createImportedNameNode('Bar'),
            $duplicate,
            $this->createImportedNameNode('Baz'),
        );
    }
}
