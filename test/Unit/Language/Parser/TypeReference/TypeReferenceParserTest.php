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
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\InvalidTypeReferenceNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeReferenceNode;
use PackageFactory\ComponentEngine\Language\Parser\TypeReference\TypeReferenceParser;
use PackageFactory\ComponentEngine\Language\AST\NodeAttributes\NodeAttributes;
use PackageFactory\ComponentEngine\Language\Parser\ParserException;
use PackageFactory\ComponentEngine\Language\Parser\TypeReference\TypeReferenceCouldNotBeParsed;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PackageFactory\ComponentEngine\Parser\Source\Path;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Tokenizer;
use PHPUnit\Framework\TestCase;

final class TypeReferenceParserTest extends TestCase
{
    /**
     * @test
     */
    public function producesAstNodeForSimpleTypeReference(): void
    {
        $typeReferenceParser = new TypeReferenceParser();
        $tokens = Tokenizer::fromSource(Source::fromString('Foo'))->getIterator();

        $expectedTypeReferenceNode = new TypeReferenceNode(
            attributes: new NodeAttributes(
                pathToSource: Path::fromString(':memory:'),
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 2)
                )
            ),
            name: TypeName::from('Foo'),
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
    public function producesAstNodeForArrayTypeReference(): void
    {
        $typeReferenceParser = new TypeReferenceParser();
        $tokens = Tokenizer::fromSource(Source::fromString('Foo[]'))->getIterator();

        $expectedTypeReferenceNode = new TypeReferenceNode(
            attributes: new NodeAttributes(
                pathToSource: Path::fromString(':memory:'),
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 4)
                )
            ),
            name: TypeName::from('Foo'),
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
    public function producesAstNodeForOptionalTypeReference(): void
    {
        $typeReferenceParser = new TypeReferenceParser();
        $tokens = Tokenizer::fromSource(Source::fromString('?Foo'))->getIterator();

        $expectedTypeReferenceNode = new TypeReferenceNode(
            attributes: new NodeAttributes(
                pathToSource: Path::fromString(':memory:'),
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 3)
                )
            ),
            name: TypeName::from('Foo'),
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
    public function throwsParserExceptionWhenInvalidTypeReferenceOccurs(): void
    {
        $typeReferenceParser = new TypeReferenceParser();
        $tokens = Tokenizer::fromSource(Source::fromString('?Foo[]'))->getIterator();
        $startingToken = $tokens->current();

        $this->expectException(ParserException::class);
        $this->expectExceptionObject(
            TypeReferenceCouldNotBeParsed::becauseOfInvalidTypeReferenceNode(
                cause: InvalidTypeReferenceNode::becauseItWasOptionalAndArrayAtTheSameTime(
                    affectedTypeName: TypeName::from('Foo'),
                    attributesOfAffectedNode: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            new Position(0, 0),
                            new Position(0, 4)
                        )
                    ),
                ),
                affectedToken: $startingToken
            )
        );

        $typeReferenceParser->parse($tokens);
    }
}
