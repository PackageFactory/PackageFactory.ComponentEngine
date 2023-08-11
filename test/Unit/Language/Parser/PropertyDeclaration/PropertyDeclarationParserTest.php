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
use PackageFactory\ComponentEngine\Language\Lexer\Lexer;
use PackageFactory\ComponentEngine\Language\Parser\PropertyDeclaration\PropertyDeclarationParser;
use PackageFactory\ComponentEngine\Test\Unit\Language\Parser\ParserTestCase;

final class PropertyDeclarationParserTest extends ParserTestCase
{
    /**
     * @test
     */
    public function parsesPropertyDeclarationWithSimpleType(): void
    {
        $propertyDeclarationParser = PropertyDeclarationParser::singleton();
        $lexer = new Lexer('foo: Bar');

        $expectedPropertyDeclarationNode = new PropertyDeclarationNode(
            rangeInSource: $this->range([0, 0], [0, 7]),
            name: new PropertyNameNode(
                rangeInSource: $this->range([0, 0], [0, 2]),
                value: PropertyName::from('foo')
            ),
            type: new TypeReferenceNode(
                rangeInSource: $this->range([0, 5], [0, 7]),
                names: new TypeNameNodes(
                    new TypeNameNode(
                        rangeInSource: $this->range([0, 5], [0, 7]),
                        value: TypeName::from('Bar')
                    )
                ),
                isArray: false,
                isOptional: false
            )
        );

        $this->assertEquals(
            $expectedPropertyDeclarationNode,
            $propertyDeclarationParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesPropertyDeclarationWithOptionalType(): void
    {
        $propertyDeclarationParser = PropertyDeclarationParser::singleton();
        $lexer = new Lexer('foo: ?Bar');

        $expectedPropertyDeclarationNode = new PropertyDeclarationNode(
            rangeInSource: $this->range([0, 0], [0, 8]),
            name: new PropertyNameNode(
                rangeInSource: $this->range([0, 0], [0, 2]),
                value: PropertyName::from('foo')
            ),
            type: new TypeReferenceNode(
                rangeInSource: $this->range([0, 5], [0, 8]),
                names: new TypeNameNodes(
                    new TypeNameNode(
                        rangeInSource: $this->range([0, 6], [0, 8]),
                        value: TypeName::from('Bar')
                    )
                ),
                isArray: false,
                isOptional: true
            )
        );

        $this->assertEquals(
            $expectedPropertyDeclarationNode,
            $propertyDeclarationParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesPropertyDeclarationWithArrayType(): void
    {
        $propertyDeclarationParser = PropertyDeclarationParser::singleton();
        $lexer = new Lexer('foo: Bar[]');

        $expectedPropertyDeclarationNode = new PropertyDeclarationNode(
            rangeInSource: $this->range([0, 0], [0, 9]),
            name: new PropertyNameNode(
                rangeInSource: $this->range([0, 0], [0, 2]),
                value: PropertyName::from('foo')
            ),
            type: new TypeReferenceNode(
                rangeInSource: $this->range([0, 5], [0, 9]),
                names: new TypeNameNodes(
                    new TypeNameNode(
                        rangeInSource: $this->range([0, 5], [0, 7]),
                        value: TypeName::from('Bar')
                    )
                ),
                isArray: true,
                isOptional: false
            )
        );

        $this->assertEquals(
            $expectedPropertyDeclarationNode,
            $propertyDeclarationParser->parse($lexer)
        );
    }

    /**
     * @test
     */
    public function parsesPropertyDeclarationWithUnionType(): void
    {
        $propertyDeclarationParser = PropertyDeclarationParser::singleton();
        $lexer = new Lexer('foo: Bar|Baz|Qux');

        $expectedPropertyDeclarationNode = new PropertyDeclarationNode(
            rangeInSource: $this->range([0, 0], [0, 15]),
            name: new PropertyNameNode(
                rangeInSource: $this->range([0, 0], [0, 2]),
                value: PropertyName::from('foo')
            ),
            type: new TypeReferenceNode(
                rangeInSource: $this->range([0, 5], [0, 15]),
                names: new TypeNameNodes(
                    new TypeNameNode(
                        rangeInSource: $this->range([0, 5], [0, 7]),
                        value: TypeName::from('Bar')
                    ),
                    new TypeNameNode(
                        rangeInSource: $this->range([0, 9], [0, 11]),
                        value: TypeName::from('Baz')
                    ),
                    new TypeNameNode(
                        rangeInSource: $this->range([0, 13], [0, 15]),
                        value: TypeName::from('Qux')
                    )
                ),
                isArray: false,
                isOptional: false
            )
        );

        $this->assertEquals(
            $expectedPropertyDeclarationNode,
            $propertyDeclarationParser->parse($lexer)
        );
    }
}
