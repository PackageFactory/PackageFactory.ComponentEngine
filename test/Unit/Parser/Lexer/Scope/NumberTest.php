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

use PackageFactory\ComponentEngine\Parser\Lexer\Scope\Number;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;
use PackageFactory\ComponentEngine\Test\Util\TokenizerTestTrait;
use PHPUnit\Framework\TestCase;

final class NumberTest extends TestCase
{
    use TokenizerTestTrait;

    /**
     * @return array<string, array<int, string>>
     */
    public function happyPathProvider(): array
    {
        return [
            ' 0' => ['0'],
            ' 1234567890' => ['1234567890'],
            ' 42' => ['42'],
            ' 0b10000000000000000000000000000000' => ['0b10000000000000000000000000000000'],
            ' 0b01111111100000000000000000000000' => ['0b01111111100000000000000000000000'],
            ' 0B00000000011111111111111111111111' => ['0B00000000011111111111111111111111'],
            ' 0o755' => ['0o755'],
            ' 0o644' => ['0o644'],
            ' 0xFFFFFFFFFFFFFFFFF' => ['0xFFFFFFFFFFFFFFFFF'],
            ' 0x123456789ABCDEF' => ['0x123456789ABCDEF'],
            ' 0xA' => ['0xA'],
            ' 1E3' => ['1E3'],
            ' 2e6' => ['2e6'],
            ' 0.1e2' => ['0.1e2'],
            ' .22' => ['.22'],
        ];
    }

    /**
     * @dataProvider happyPathProvider
     * @test
     * @small
     * @param string $number
     * @return void
     */
    public function testHappyPath(string $number): void
    {
        $iterator = SourceIterator::fromSource(Source::fromString($number));

        $this->assertTokenStream([
            [TokenType::NUMBER, $number]
        ], Number::tokenize($iterator));
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function exitPathProvider(): array
    {
        return [
            ' 0b100121001' => ['0b100121001', '0b1001'],
            ' 0b1001 1001' => ['0b1001 1001', '0b1001'],
            ' 0b1001o1001' => ['0b1001o1001', '0b1001'],
            ' 0b1001b1001' => ['0b1001b1001', '0b1001'],
            ' 0b1001x1001' => ['0b1001x1001', '0b1001'],
            ' 0o734518125461' => ['0o734518125461', '0o73451'],
            ' 0o734519125461' => ['0o734519125461', '0o73451'],
            ' 0o73451 125461' => ['0o73451 125461', '0o73451'],
            ' 0o73451o125461' => ['0o73451o125461', '0o73451'],
            ' 0o73451x125461' => ['0o73451x125461', '0o73451'],
            ' 0xFF65A3GBC43AF' => ['0xFF65A3GBC43AF', '0xFF65A3'],
            ' 0xFF65A3 BC43AF' => ['0xFF65A3 BC43AF', '0xFF65A3'],
            ' 0xFF65A3bBC43AF' => ['0xFF65A3bBC43AF', '0xFF65A3'],
            ' 0xFF65A3oBC43AF' => ['0xFF65A3oBC43AF', '0xFF65A3'],
            ' 0xFF65A3xBC43AF' => ['0xFF65A3xBC43AF', '0xFF65A3'],
        ];
    }

    /**
     * @dataProvider exitPathProvider
     * @test
     * @small
     * @param string $number
     * @param string $expected
     * @return void
     */
    public function testExitPath(string $number, string $expected): void
    {
        $iterator = SourceIterator::fromSource(Source::fromString($number));

        $this->assertTokenStream([
            [TokenType::NUMBER, $expected]
        ], Number::tokenize($iterator));
    }
}
