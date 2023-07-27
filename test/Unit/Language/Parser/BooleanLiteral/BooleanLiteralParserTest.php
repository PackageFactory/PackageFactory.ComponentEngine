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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Parser\BooleanLiteral;

use PackageFactory\ComponentEngine\Language\AST\Node\BooleanLiteral\BooleanLiteralNode;
use PackageFactory\ComponentEngine\Language\Parser\BooleanLiteral\BooleanLiteralParser;
use PackageFactory\ComponentEngine\Language\Shared\NodeAttributes\NodeAttributes;
use PackageFactory\ComponentEngine\Parser\Source\Boundaries;
use PackageFactory\ComponentEngine\Parser\Source\Path;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Tokenizer;
use PHPUnit\Framework\TestCase;

final class BooleanLiteralParserTest extends TestCase
{
    /**
     * @test
     */
    public function producesAstNodeForTrueIfGivenOneTrueToken(): void
    {
        $booleanLiteralParser = new BooleanLiteralParser();
        $tokens = Tokenizer::fromSource(Source::fromString('true'))->getIterator();

        $expectedBooleanLiteralNode = new BooleanLiteralNode(
            attributes: new NodeAttributes(
                pathToSource: Path::fromString(':memory:'),
                rangeInSource: Boundaries::fromPositions(
                    Position::create(0, 0, 0),
                    Position::create(3, 0, 3)
                )
            ),
            value: true
        );

        $this->assertEquals(
            $expectedBooleanLiteralNode,
            $booleanLiteralParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function producesAstNodeForFalseIfGivenOneFalseToken(): void
    {
        $booleanLiteralParser = new BooleanLiteralParser();
        $tokens = Tokenizer::fromSource(Source::fromString('false'))->getIterator();

        $expectedBooleanLiteralNode = new BooleanLiteralNode(
            attributes: new NodeAttributes(
                pathToSource: Path::fromString(':memory:'),
                rangeInSource: Boundaries::fromPositions(
                    Position::create(0, 0, 0),
                    Position::create(4, 0, 4)
                )
            ),
            value: false
        );

        $this->assertEquals(
            $expectedBooleanLiteralNode,
            $booleanLiteralParser->parse($tokens)
        );
    }
}
