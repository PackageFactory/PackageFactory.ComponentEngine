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

namespace PackageFactory\ComponentEngine\Test\Unit\Target\Php\Transpiler\BooleanLiteral;

use PackageFactory\ComponentEngine\Target\Php\Transpiler\BooleanLiteral\BooleanLiteralTranspiler;
use PackageFactory\ComponentEngine\Test\Unit\Language\ASTNodeFixtures;
use PHPUnit\Framework\TestCase;

final class BooleanLiteralTranspilerTest extends TestCase
{
    /**
     * @return array<string,mixed>
     */
    public static function booleanLiteralExamples(): array
    {
        return [
            'true' => ['true', 'true'],
            'false' => ['false', 'false'],
        ];
    }

    /**
     * @dataProvider booleanLiteralExamples
     * @test
     * @param string $booleanLiteralAsString
     * @param string $expectedTranspilationResult
     * @return void
     */
    public function transpilesBooleanLiteralNodes(string $booleanLiteralAsString, string $expectedTranspilationResult): void
    {
        $booleanLiteralTranspiler = new BooleanLiteralTranspiler();
        $booleanLiteralNode = ASTNodeFixtures::BooleanLiteral($booleanLiteralAsString);

        $actualTranspilationResult = $booleanLiteralTranspiler->transpile(
            $booleanLiteralNode
        );

        $this->assertEquals(
            $expectedTranspilationResult,
            $actualTranspilationResult
        );
    }
}
