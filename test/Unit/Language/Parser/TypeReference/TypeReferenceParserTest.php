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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Parser\TypeReference;

use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\Domain\TypeName\TypeNames;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\InvalidTypeNameNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\InvalidTypeReferenceNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeNameNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeReferenceNode;
use PackageFactory\ComponentEngine\Language\Parser\TypeReference\TypeReferenceParser;
use PackageFactory\ComponentEngine\Language\Parser\ParserException;
use PackageFactory\ComponentEngine\Language\Parser\TypeReference\TypeReferenceCouldNotBeParsed;
use PackageFactory\ComponentEngine\Test\Unit\Language\Parser\ParserTestCase;

final class TypeReferenceParserTest extends ParserTestCase
{
    /**
     * @test
     */
    public function parsesSimpleTypeReference(): void
    {
        $typeReferenceParser = new TypeReferenceParser();
        $tokens = $this->createTokenIterator('Foo');

        $expectedTypeReferenceNode = new TypeReferenceNode(
            rangeInSource: $this->range([0, 0], [0, 2]),
            names: new TypeNameNodes(
                new TypeNameNode(
                    rangeInSource: $this->range([0, 0], [0, 2]),
                    value: TypeName::from('Foo')
                )
            ),
            isArray: false,
            isOptional: false
        );

        $this->assertEquals(
            $expectedTypeReferenceNode,
            $typeReferenceParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesArrayTypeReference(): void
    {
        $typeReferenceParser = new TypeReferenceParser();
        $tokens = $this->createTokenIterator('Foo[]');

        $expectedTypeReferenceNode = new TypeReferenceNode(
            rangeInSource: $this->range([0, 0], [0, 4]),
            names: new TypeNameNodes(
                new TypeNameNode(
                    rangeInSource: $this->range([0, 0], [0, 2]),
                    value: TypeName::from('Foo')
                )
            ),
            isArray: true,
            isOptional: false
        );

        $this->assertEquals(
            $expectedTypeReferenceNode,
            $typeReferenceParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesOptionalTypeReference(): void
    {
        $typeReferenceParser = new TypeReferenceParser();
        $tokens = $this->createTokenIterator('?Foo');

        $expectedTypeReferenceNode = new TypeReferenceNode(
            rangeInSource: $this->range([0, 0], [0, 3]),
            names: new TypeNameNodes(
                new TypeNameNode(
                    rangeInSource: $this->range([0, 1], [0, 3]),
                    value: TypeName::from('Foo')
                )
            ),
            isArray: false,
            isOptional: true
        );

        $this->assertEquals(
            $expectedTypeReferenceNode,
            $typeReferenceParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesUnionTypeReference(): void
    {
        $typeReferenceParser = new TypeReferenceParser();
        $tokens = $this->createTokenIterator('Foo|Bar|Baz');

        $expectedTypeReferenceNode = new TypeReferenceNode(
            rangeInSource: $this->range([0, 0], [0, 10]),
            names: new TypeNameNodes(
                new TypeNameNode(
                    rangeInSource: $this->range([0, 0], [0, 2]),
                    value: TypeName::from('Foo')
                ),
                new TypeNameNode(
                    rangeInSource: $this->range([0, 4], [0, 6]),
                    value: TypeName::from('Bar')
                ),
                new TypeNameNode(
                    rangeInSource: $this->range([0, 8], [0, 10]),
                    value: TypeName::from('Baz')
                )
            ),
            isArray: false,
            isOptional: false
        );

        $this->assertEquals(
            $expectedTypeReferenceNode,
            $typeReferenceParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function throwsIfInvalidTypeReferenceOccurs(): void
    {
        $typeReferenceParser = new TypeReferenceParser();
        $tokens = $this->createTokenIterator('?Foo[]');

        $this->expectException(ParserException::class);
        $this->expectExceptionObject(
            TypeReferenceCouldNotBeParsed::becauseOfInvalidTypeReferenceNode(
                cause: InvalidTypeReferenceNode::becauseItWasOptionalAndArrayAtTheSameTime(
                    affectedTypeNames: new TypeNames(TypeName::from('Foo')),
                    affectedRangeInSource: $this->range([0, 0], [0, 4]),
                )
            )
        );

        $typeReferenceParser->parse($tokens);
    }

    /**
     * @test
     */
    public function throwsIfDuplicatesOccurInUnionTypeReference(): void
    {
        $typeReferenceParser = new TypeReferenceParser();
        $tokens = $this->createTokenIterator('Foo|Bar|Foo|Baz');

        $this->expectException(ParserException::class);
        $this->expectExceptionObject(
            TypeReferenceCouldNotBeParsed::becauseOfInvalidTypeTypeNameNodes(
                cause: InvalidTypeNameNodes::becauseTheyContainDuplicates(
                    duplicateTypeNameNode: new TypeNameNode(
                        rangeInSource: $this->range([0, 9], [0, 11]),
                        value: TypeName::from('Foo')
                    )
                )
            )
        );

        $typeReferenceParser->parse($tokens);
    }
}
