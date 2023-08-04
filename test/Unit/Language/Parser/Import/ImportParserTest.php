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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Parser\Import;

use PackageFactory\ComponentEngine\Domain\VariableName\VariableName;
use PackageFactory\ComponentEngine\Language\AST\Node\Import\ImportedNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Import\ImportedNameNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Import\ImportNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Import\InvalidImportedNameNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\StringLiteral\StringLiteralNode;
use PackageFactory\ComponentEngine\Language\Parser\Import\ImportCouldNotBeParsed;
use PackageFactory\ComponentEngine\Language\Parser\Import\ImportParser;
use PackageFactory\ComponentEngine\Language\Parser\ParserException;
use PackageFactory\ComponentEngine\Test\Unit\Language\Parser\ParserTestCase;

final class ImportParserTest extends ParserTestCase
{
    /**
     * @test
     */
    public function parsesImportWithOneName(): void
    {
        $importParser = new ImportParser();
        $tokens = $this->createTokenIterator(
            'from "/some/where/in/the/filesystem" import { Foo }'
        );

        $expectedImportNode = new ImportNode(
            rangeInSource: $this->range([0, 0], [0, 50]),
            path: new StringLiteralNode(
                rangeInSource: $this->range([0, 6], [0, 34]),
                value: '/some/where/in/the/filesystem'
            ),
            names: new ImportedNameNodes(
                new ImportedNameNode(
                    rangeInSource: $this->range([0, 46], [0, 48]),
                    value: VariableName::from('Foo')
                )
            )
        );

        $this->assertEquals(
            $expectedImportNode,
            $importParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesImportWithMultipleNames(): void
    {
        $importParser = new ImportParser();
        $tokens = $this->createTokenIterator(
            'from "./some/other.component" import { Foo, Bar, Baz }'
        );

        $expectedImportNode = new ImportNode(
            rangeInSource: $this->range([0, 0], [0, 53]),
            path: new StringLiteralNode(
                rangeInSource: $this->range([0, 6], [0, 27]),
                value: './some/other.component'
            ),
            names: new ImportedNameNodes(
                new ImportedNameNode(
                    rangeInSource: $this->range([0, 39], [0, 41]),
                    value: VariableName::from('Foo')
                ),
                new ImportedNameNode(
                    rangeInSource: $this->range([0, 44], [0, 46]),
                    value: VariableName::from('Bar')
                ),
                new ImportedNameNode(
                    rangeInSource: $this->range([0, 49], [0, 51]),
                    value: VariableName::from('Baz')
                )
            )
        );

        $this->assertEquals(
            $expectedImportNode,
            $importParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function emptyImportIsNotAllowed(): void
    {
        $this->assertThrowsParserException(
            function () {
                $importParser = new ImportParser();
                $tokens = $this->createTokenIterator(
                    'from "/some/where" import {}'
                );

                $importParser->parse($tokens);
            },
            ImportCouldNotBeParsed::becauseOfInvalidImportedNameNodes(
                cause: InvalidImportedNameNodes::becauseTheyWereEmpty(),
                affectedRangeInSource: $this->range([0, 26], [0, 26])
            )
        );
    }

    /**
     * @test
     */
    public function duplicateImportsAreNotAllowed(): void
    {
        $this->assertThrowsParserException(
            function () {
                $importParser = new ImportParser();
                $tokens = $this->createTokenIterator(
                    'from "/some/where" import { Foo, Bar, Baz, Bar, Qux }'
                );

                $importParser->parse($tokens);
            },
            ImportCouldNotBeParsed::becauseOfInvalidImportedNameNodes(
                cause: InvalidImportedNameNodes::becauseTheyContainDuplicates(
                    duplicateImportedNameNode: new ImportedNameNode(
                        rangeInSource: $this->range([0, 43], [0, 45]),
                        value: VariableName::from('Bar')
                    )
                ),
                affectedRangeInSource: $this->range([0, 43], [0, 45]),
            )
        );
    }
}
