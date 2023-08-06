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

namespace PackageFactory\ComponentEngine\Test\Integration;

use PackageFactory\ComponentEngine\Language\Parser\Module\ModuleParser;
use PackageFactory\ComponentEngine\Module\Loader\ModuleFile\ModuleFileLoader;
use PackageFactory\ComponentEngine\Parser\Source\Path;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Tokenizer;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\Module\ModuleTranspiler;
use PackageFactory\ComponentEngine\Test\Unit\Target\Php\Transpiler\Module\ModuleTestStrategy;
use PackageFactory\ComponentEngine\TypeSystem\Scope\GlobalScope\GlobalScope;
use PHPUnit\Framework\TestCase;

final class PhpTranspilerIntegrationTest extends TestCase
{
    /**
     * @return array<string,mixed>
     */
    public static function transpilerExamples(): array
    {
        return [
            'Component' => ["Component"],
            'ComponentWithKeywords' => ["ComponentWithKeywords"],
            'ComponentWithNesting' => ["ComponentWithNesting"],
            'Enum' => ["Enum"],
            'EnumWithStringValue' => ["EnumWithStringValue"],
            'EnumWithNumberValue' => ["EnumWithNumberValue"],
            'Expression' => ["Expression"],
            'ImportExport' => ["ImportExport"],
            'Match' => ["Match"],
            'Numbers' => ["Numbers"],
            'Struct' => ["Struct"],
            'StructWithOptionals' => ["StructWithOptionals"],
            'TemplateLiteral' => ["TemplateLiteral"],
        ];
    }

    /**
     * @dataProvider transpilerExamples
     * @test
     * @small
     * @param string $example
     * @return void
     */
    public function testTranspiler(string $example): void
    {
        $sourcePath = Path::fromString(__DIR__ . '/Examples/' . $example . '/' . $example . '.afx');
        $source = Source::fromFile($sourcePath->value);
        $tokenizer = Tokenizer::fromSource($source);
        $tokens = $tokenizer->getIterator();

        $module = ModuleParser::singleton()->parse($tokens);

        $expected = file_get_contents(__DIR__ . '/Examples/' . $example . '/' . $example . '.php');

        $transpiler = new ModuleTranspiler(
            loader: new ModuleFileLoader(
                sourcePath: $sourcePath
            ),
            globalScope: GlobalScope::singleton(),
            strategy: new ModuleTestStrategy()
        );

        $this->assertEquals($expected, $transpiler->transpile($module));
    }
}
