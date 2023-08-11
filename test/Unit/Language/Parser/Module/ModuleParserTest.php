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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Parser\Module;

use PackageFactory\ComponentEngine\Domain\StructName\StructName;
use PackageFactory\ComponentEngine\Domain\VariableName\VariableName;
use PackageFactory\ComponentEngine\Language\AST\Node\Export\ExportNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Import\ImportedNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Import\ImportedNameNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Import\ImportNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Import\ImportNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Module\ModuleNode;
use PackageFactory\ComponentEngine\Language\AST\Node\PropertyDeclaration\PropertyDeclarationNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\StringLiteral\StringLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StructDeclaration\StructDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StructDeclaration\StructNameNode;
use PackageFactory\ComponentEngine\Language\Lexer\Lexer;
use PackageFactory\ComponentEngine\Language\Lexer\LexerException;
use PackageFactory\ComponentEngine\Language\Parser\Module\ModuleCouldNotBeParsed;
use PackageFactory\ComponentEngine\Language\Parser\Module\ModuleParser;
use PackageFactory\ComponentEngine\Test\Unit\Language\Parser\ParserTestCase;

final class ModuleParserTest extends ParserTestCase
{
    /**
     * @test
     */
    public function parsesModuleWithNoImports(): void
    {
        $moduleParser = ModuleParser::singleton();
        $moduleAsString = <<<AFX
        export struct Foo {}
        AFX;
        $lexer = new Lexer($moduleAsString);

        $expectedModuleNode = new ModuleNode(
            rangeInSource: $this->range([0, 0], [0, 19]),
            imports: new ImportNodes(),
            export: new ExportNode(
                rangeInSource: $this->range([0, 0], [0, 19]),
                declaration: new StructDeclarationNode(
                    rangeInSource: $this->range([0, 7], [0, 19]),
                    name: new StructNameNode(
                        rangeInSource: $this->range([0, 14], [0, 16]),
                        value: StructName::from('Foo')
                    ),
                    properties: new PropertyDeclarationNodes()
                )
            )
        );

        $this->assertEquals(
            $expectedModuleNode,
            $moduleParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesModuleWithOneImport(): void
    {
        $moduleParser = ModuleParser::singleton();
        $moduleAsString = <<<AFX
        from "/some/where" import { Foo, Bar }

        export struct Baz {}
        AFX;
        $lexer = new Lexer($moduleAsString);

        $expectedModuleNode = new ModuleNode(
            rangeInSource: $this->range([0, 0], [2, 19]),
            imports: new ImportNodes(
                new ImportNode(
                    rangeInSource: $this->range([0, 0], [0, 37]),
                    path: new StringLiteralNode(
                        rangeInSource: $this->range([0, 5], [0, 17]),
                        value: '/some/where'
                    ),
                    names: new ImportedNameNodes(
                        new ImportedNameNode(
                            rangeInSource: $this->range([0, 28], [0, 30]),
                            value: VariableName::from('Foo')
                        ),
                        new ImportedNameNode(
                            rangeInSource: $this->range([0, 33], [0, 35]),
                            value: VariableName::from('Bar')
                        )
                    )
                )
            ),
            export: new ExportNode(
                rangeInSource: $this->range([2, 0], [2, 19]),
                declaration: new StructDeclarationNode(
                    rangeInSource: $this->range([2, 7], [2, 19]),
                    name: new StructNameNode(
                        rangeInSource: $this->range([2, 14], [2, 16]),
                        value: StructName::from('Baz')
                    ),
                    properties: new PropertyDeclarationNodes()
                )
            )
        );

        $this->assertEquals(
            $expectedModuleNode,
            $moduleParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesModuleWithMultipleImports(): void
    {
        $moduleParser = ModuleParser::singleton();
        $moduleAsString = <<<AFX
        from "/some/where" import { Foo, Bar }
        from "/some/where/else" import { Baz }
        from "./here" import { Qux, Quux }

        export struct Corge {}
        AFX;
        $lexer = new Lexer($moduleAsString);

        $expectedModuleNode = new ModuleNode(
            rangeInSource: $this->range([0, 0], [4, 21]),
            imports: new ImportNodes(
                new ImportNode(
                    rangeInSource: $this->range([0, 0], [0, 37]),
                    path: new StringLiteralNode(
                        rangeInSource: $this->range([0, 5], [0, 17]),
                        value: '/some/where'
                    ),
                    names: new ImportedNameNodes(
                        new ImportedNameNode(
                            rangeInSource: $this->range([0, 28], [0, 30]),
                            value: VariableName::from('Foo')
                        ),
                        new ImportedNameNode(
                            rangeInSource: $this->range([0, 33], [0, 35]),
                            value: VariableName::from('Bar')
                        )
                    )
                ),
                new ImportNode(
                    rangeInSource: $this->range([1, 0], [1, 37]),
                    path: new StringLiteralNode(
                        rangeInSource: $this->range([1, 5], [1, 22]),
                        value: '/some/where/else'
                    ),
                    names: new ImportedNameNodes(
                        new ImportedNameNode(
                            rangeInSource: $this->range([1, 33], [1, 35]),
                            value: VariableName::from('Baz')
                        ),
                    )
                ),
                new ImportNode(
                    rangeInSource: $this->range([2, 0], [2, 33]),
                    path: new StringLiteralNode(
                        rangeInSource: $this->range([2, 5], [2, 12]),
                        value: './here'
                    ),
                    names: new ImportedNameNodes(
                        new ImportedNameNode(
                            rangeInSource: $this->range([2, 23], [2, 25]),
                            value: VariableName::from('Qux')
                        ),
                        new ImportedNameNode(
                            rangeInSource: $this->range([2, 28], [2, 31]),
                            value: VariableName::from('Quux')
                        )
                    )
                ),

            ),
            export: new ExportNode(
                rangeInSource: $this->range([4, 0], [4, 21]),
                declaration: new StructDeclarationNode(
                    rangeInSource: $this->range([4, 7], [4, 21]),
                    name: new StructNameNode(
                        rangeInSource: $this->range([4, 14], [4, 18]),
                        value: StructName::from('Corge')
                    ),
                    properties: new PropertyDeclarationNodes()
                )
            )
        );

        $this->assertEquals(
            $expectedModuleNode,
            $moduleParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function toleratesCommentsAndSpacesInBetweenStatements(): void
    {
        $moduleParser = ModuleParser::singleton();
        $moduleAsString = <<<AFX

        #
        # Comment before import
        #

        from "/some/where" import { Foo, Bar }

        #
        # Comment in between import and export
        #

        export struct Baz {}

        #
        # Comment after export
        #

        AFX;
        $lexer = new Lexer($moduleAsString);

        $expectedModuleNode = new ModuleNode(
            rangeInSource: $this->range([0, 0], [11, 19]),
            imports: new ImportNodes(
                new ImportNode(
                    rangeInSource: $this->range([5, 0], [5, 37]),
                    path: new StringLiteralNode(
                        rangeInSource: $this->range([5, 5], [5, 17]),
                        value: '/some/where'
                    ),
                    names: new ImportedNameNodes(
                        new ImportedNameNode(
                            rangeInSource: $this->range([5, 28], [5, 30]),
                            value: VariableName::from('Foo')
                        ),
                        new ImportedNameNode(
                            rangeInSource: $this->range([5, 33], [5, 35]),
                            value: VariableName::from('Bar')
                        )
                    )
                )
            ),
            export: new ExportNode(
                rangeInSource: $this->range([11, 0], [11, 19]),
                declaration: new StructDeclarationNode(
                    rangeInSource: $this->range([11, 7], [11, 19]),
                    name: new StructNameNode(
                        rangeInSource: $this->range([11, 14], [11, 16]),
                        value: StructName::from('Baz')
                    ),
                    properties: new PropertyDeclarationNodes()
                )
            )
        );

        $this->assertEquals(
            $expectedModuleNode,
            $moduleParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function throwsIfExceedingCharactersOccur(): void
    {
        $this->assertThrowsParserException(
            function () {
                $moduleParser = ModuleParser::singleton();
                $moduleAsString = <<<AFX
                from "/some/where" import { Foo, Bar }
                from "/some/where/else" import { Baz }

                export struct Qux {}
                export struct Quux {}
                AFX;
                $lexer = new Lexer($moduleAsString);

                $moduleParser->parse($lexer);
            },
            ModuleCouldNotBeParsed::becauseOfLexerException(
                cause: LexerException::becauseOfUnexpectedExceedingSource(
                    affectedRangeInSource: $this->range([4, 0], [4, 0]),
                    exceedingCharacter: 'e'
                )
            )
        );
    }
}
