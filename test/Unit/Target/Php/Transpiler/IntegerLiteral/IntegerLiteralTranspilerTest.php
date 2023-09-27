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

namespace PackageFactory\ComponentEngine\Test\Unit\Target\Php\Transpiler\IntegerLiteral;

use PackageFactory\ComponentEngine\Target\Php\Transpiler\IntegerLiteral\IntegerLiteralTranspiler;
use PackageFactory\ComponentEngine\Test\Unit\Language\ASTNodeFixtures;
use PHPUnit\Framework\TestCase;

final class IntegerLiteralTranspilerTest extends TestCase
{
    /**
     * @return array<string,mixed>
     */
    public static function integerLiteralExamples(): array
    {
        return [
            // Decimal
            ' 0 ' => ['0', '0'],
            ' 1234567890 ' => ['1234567890', '1234567890'],
            ' 42 ' => ['42', '42'],

            // Binary
            ' 0b10000000000000000000000000000000 ' => ['0b10000000000000000000000000000000', '0b10000000000000000000000000000000'],
            ' 0b01111111100000000000000000000000 ' => ['0b01111111100000000000000000000000', '0b01111111100000000000000000000000'],

            // Octal
            ' 0o755 ' => ['0o755', '0o755'],
            ' 0o644 ' => ['0o644', '0o644'],

            // Hexadecimal
            ' 0xFFFFFFFFFFFFFFFFF ' => ['0xFFFFFFFFFFFFFFFFF', '0xFFFFFFFFFFFFFFFFF'],
            ' 0x123456789ABCDEF ' => ['0x123456789ABCDEF', '0x123456789ABCDEF'],
            ' 0xA ' => ['0xA', '0xA'],
        ];
    }

    /**
     * @dataProvider integerLiteralExamples
     * @test
     * @param string $integerLiteralAsString
     * @param string $expectedTranspilationResult
     * @return void
     */
    public function transpilesIntegerLiteralNodes(string $integerLiteralAsString, string $expectedTranspilationResult): void
    {
        $integerLiteralTranspiler = new IntegerLiteralTranspiler();
        $integerLiteralNode = ASTNodeFixtures::IntegerLiteral($integerLiteralAsString);

        $actualTranspilationResult = $integerLiteralTranspiler->transpile(
            $integerLiteralNode
        );

        $this->assertEquals(
            $expectedTranspilationResult,
            $actualTranspilationResult
        );
    }
}
