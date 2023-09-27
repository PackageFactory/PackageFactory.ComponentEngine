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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Parser\Export;

use PackageFactory\ComponentEngine\Domain\ComponentName\ComponentName;
use PackageFactory\ComponentEngine\Domain\EnumMemberName\EnumMemberName;
use PackageFactory\ComponentEngine\Domain\EnumName\EnumName;
use PackageFactory\ComponentEngine\Domain\PropertyName\PropertyName;
use PackageFactory\ComponentEngine\Domain\StructName\StructName;
use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\Domain\VariableName\VariableName;
use PackageFactory\ComponentEngine\Language\AST\Node\ComponentDeclaration\ComponentDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\ComponentDeclaration\ComponentNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumMemberDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumMemberDeclarationNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumMemberNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Export\ExportNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Expression\ExpressionNode;
use PackageFactory\ComponentEngine\Language\AST\Node\PropertyDeclaration\PropertyDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\PropertyDeclaration\PropertyDeclarationNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\PropertyDeclaration\PropertyNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StructDeclaration\StructDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StructDeclaration\StructNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeNameNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeReferenceNode;
use PackageFactory\ComponentEngine\Language\AST\Node\ValueReference\ValueReferenceNode;
use PackageFactory\ComponentEngine\Language\Parser\Export\ExportCouldNotBeParsed;
use PackageFactory\ComponentEngine\Language\Parser\Export\ExportParser;
use PackageFactory\ComponentEngine\Parser\Source\Path;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenTypes;
use PackageFactory\ComponentEngine\Test\Unit\Language\Parser\ParserTestCase;

final class ExportParserTest extends ParserTestCase
{
    /**
     * @test
     */
    public function parsesComponentExport(): void
    {
        $exportParser = ExportParser::singleton();
        $tokens = $this->createTokenIterator(
            'export component Foo { return bar }'
        );

        $expectedExportNode = new ExportNode(
            rangeInSource: $this->range([0, 0], [0, 34]),
            declaration: new ComponentDeclarationNode(
                rangeInSource: $this->range([0, 7], [0, 34]),
                name: new ComponentNameNode(
                    rangeInSource: $this->range([0, 17], [0, 19]),
                    value: ComponentName::from('Foo')
                ),
                props: new PropertyDeclarationNodes(),
                return: new ExpressionNode(
                    rangeInSource: $this->range([0, 30], [0, 32]),
                    root: new ValueReferenceNode(
                        rangeInSource: $this->range([0, 30], [0, 32]),
                        name: VariableName::from('bar')
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedExportNode,
            $exportParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesEnumExport(): void
    {
        $exportParser = ExportParser::singleton();
        $tokens = $this->createTokenIterator(
            'export enum Foo { BAR }'
        );

        $expectedExportNode = new ExportNode(
            rangeInSource: $this->range([0, 0], [0, 22]),
            declaration: new EnumDeclarationNode(
                rangeInSource: $this->range([0, 7], [0, 22]),
                name: new EnumNameNode(
                    rangeInSource: $this->range([0, 12], [0, 14]),
                    value: EnumName::from('Foo')
                ),
                members: new EnumMemberDeclarationNodes(
                    new EnumMemberDeclarationNode(
                        rangeInSource: $this->range([0, 18], [0, 20]),
                        name: new EnumMemberNameNode(
                            rangeInSource: $this->range([0, 18], [0, 20]),
                            value: EnumMemberName::from('BAR')
                        ),
                        value: null
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedExportNode,
            $exportParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesStructExport(): void
    {
        $exportParser = ExportParser::singleton();
        $tokens = $this->createTokenIterator(
            'export struct Foo { bar: baz }'
        );

        $expectedExportNode = new ExportNode(
            rangeInSource: $this->range([0, 0], [0, 29]),
            declaration: new StructDeclarationNode(
                rangeInSource: $this->range([0, 7], [0, 29]),
                name: new StructNameNode(
                    rangeInSource: $this->range([0, 14], [0, 16]),
                    value: StructName::from('Foo')
                ),
                properties: new PropertyDeclarationNodes(
                    new PropertyDeclarationNode(
                        rangeInSource: $this->range([0, 20], [0, 27]),
                        name: new PropertyNameNode(
                            rangeInSource: $this->range([0, 20], [0, 22]),
                            value: PropertyName::from('bar')
                        ),
                        type: new TypeReferenceNode(
                            rangeInSource: $this->range([0, 25], [0, 27]),
                            names: new TypeNameNodes(
                                new TypeNameNode(
                                    rangeInSource: $this->range([0, 25], [0, 27]),
                                    value: TypeName::from('baz')
                                )
                            ),
                            isArray: false,
                            isOptional: false
                        )
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedExportNode,
            $exportParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function throwsIfExportIsNoDeclaration(): void
    {
        $this->assertThrowsParserException(
            function () {
                $exportParser = ExportParser::singleton();
                $tokens = $this->createTokenIterator('export null');

                $exportParser->parse($tokens);
            },
            ExportCouldNotBeParsed::becauseOfUnexpectedToken(
                expectedTokenTypes: TokenTypes::from(
                    TokenType::KEYWORD_COMPONENT,
                    TokenType::KEYWORD_ENUM,
                    TokenType::KEYWORD_STRUCT
                ),
                actualToken: new Token(
                    type: TokenType::KEYWORD_NULL,
                    value: 'null',
                    boundaries: $this->range([0, 7], [0, 10]),
                    sourcePath: Path::createMemory()
                )
            )
        );
    }
}
