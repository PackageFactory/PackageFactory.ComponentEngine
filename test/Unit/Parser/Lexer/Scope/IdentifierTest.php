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

namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Lexer\Scope;

use PackageFactory\ComponentEngine\Parser\Lexer\Scope\Identifier;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;
use PackageFactory\ComponentEngine\Test\Util\TokenizerTestTrait;
use PHPUnit\Framework\TestCase;

final class IdentifierTest extends TestCase
{
    use TokenizerTestTrait;

    /**
     * @return array<string, array<int, string>>
     */
    public function provider(): array
    {
        return [
            'Foo' => ['Foo'],
            '_Foo' => ['_Foo'],
            '$Foo' => ['$Foo'],
            'foo' => ['foo'],
            '_foo' => ['_foo'],
            '$foo' => ['$foo'],
            'foo_bar' => ['foo_bar'],
            '_foo_bar' => ['_foo_bar'],
            '$foo_bar' => ['$foo_bar'],
            'Foo_Bar' => ['Foo_Bar'],
            '_Foo_Bar' => ['_Foo_Bar'],
            '$Foo_Bar' => ['$Foo_Bar'],
            'foo123' => ['foo123'],
            '_foo123' => ['_foo123'],
            '$foo123' => ['$foo123'],
            'Foo123' => ['Foo123'],
            '_Foo123' => ['_Foo123'],
            '$Foo123' => ['$Foo123'],
            'foo_123' => ['foo_123'],
            '_foo_123' => ['_foo_123'],
            '$foo_123' => ['$foo_123'],
            'Foo_123' => ['Foo_123'],
            '_Foo_123' => ['_Foo_123'],
            '$Foo_123' => ['$Foo_123'],
        ];
    }

    /**
     * @dataProvider provider
     * @test
     * @small
     * @param string $identifier
     * @return void
     */
    public function test(string $identifier): void
    {
        $iterator = SourceIterator::fromSource(Source::fromString($identifier));

        $this->assertTokenStream([
            [TokenType::IDENTIFIER, $identifier]
        ], Identifier::tokenize($iterator));
    }

    /**
     * @test
     * @small
     * @return void
     */
    public function testExitAfterDelimiter(): void
    {
        $identifier = 'foo ';
        $iterator = SourceIterator::fromSource(Source::fromString($identifier));

        $this->assertTokenStream([
            [TokenType::IDENTIFIER, 'foo']
        ], Identifier::tokenize($iterator));
    }
}
