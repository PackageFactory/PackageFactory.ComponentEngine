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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Parser\StringLiteral;

use PackageFactory\ComponentEngine\Language\AST\Node\StringLiteral\StringLiteralNode;
use PackageFactory\ComponentEngine\Language\Parser\StringLiteral\StringLiteralParser;
use PackageFactory\ComponentEngine\Language\AST\NodeAttributes\NodeAttributes;
use PackageFactory\ComponentEngine\Parser\Source\Boundaries;
use PackageFactory\ComponentEngine\Parser\Source\Path;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Tokenizer;
use PHPUnit\Framework\TestCase;

final class StringLiteralParserTest extends TestCase
{
    /**
     * @test
     */
    public function producesStringLiteralNodeForLiteralString(): void
    {
        $stringLiteralParser = new StringLiteralParser();
        $tokens = Tokenizer::fromSource(Source::fromString('"Hello World"'))->getIterator();

        $expectedStringLiteralNode = new StringLiteralNode(
            attributes: new NodeAttributes(
                pathToSource: Path::fromString(':memory:'),
                rangeInSource: Boundaries::fromPositions(
                    Position::create(1, 0, 1),
                    Position::create(11, 0, 11)
                )
            ),
            value: 'Hello World'
        );

        $this->assertEquals(
            $expectedStringLiteralNode,
            $stringLiteralParser->parse($tokens)
        );
    }
}
