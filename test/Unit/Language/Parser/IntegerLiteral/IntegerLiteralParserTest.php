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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Parser\IntegerLiteral;

use PackageFactory\ComponentEngine\Language\AST\IntegerLiteral\IntegerFormat;
use PackageFactory\ComponentEngine\Language\AST\IntegerLiteral\IntegerLiteralNode;
use PackageFactory\ComponentEngine\Language\Parser\IntegerLiteral\IntegerLiteralParser;
use PackageFactory\ComponentEngine\Language\Shared\Location\Location;
use PackageFactory\ComponentEngine\Parser\Source\Boundaries;
use PackageFactory\ComponentEngine\Parser\Source\Path;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Tokenizer;
use PHPUnit\Framework\TestCase;

final class IntegerLiteralParserTest extends TestCase
{
    /**
     * @test
     */
    public function producesIntegerLiteralNodeForDecimals(): void
    {
        $integerLiteralParser = new IntegerLiteralParser();
        $tokens = Tokenizer::fromSource(Source::fromString('1234567890'))->getIterator();

        $expectedIntegerLiteralNode = new IntegerLiteralNode(
            location: new Location(
                sourcePath: Path::fromString(':memory:'),
                boundaries: Boundaries::fromPositions(
                    Position::create(0, 0, 0),
                    Position::create(9, 0, 9)
                )
            ),
            format: IntegerFormat::DECIMAL,
            value: '1234567890'
        );

        $this->assertEquals(
            $expectedIntegerLiteralNode,
            $integerLiteralParser->parse($tokens)
        );
    }
}
