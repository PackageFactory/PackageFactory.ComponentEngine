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

use PackageFactory\ComponentEngine\Module\Loader\ModuleFile\ModuleFileLoader;
use PackageFactory\ComponentEngine\Parser\Ast\EnumDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Parser\Module\ModuleParser;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\Module\ModuleTranspiler;
use PackageFactory\ComponentEngine\Test\Unit\Target\Php\Transpiler\Module\ModuleTestStrategy;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\TypeSystem\Type\BooleanType\BooleanType;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumStaticType;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumType;
use PackageFactory\ComponentEngine\TypeSystem\Type\NumberType\NumberType;
use PackageFactory\ComponentEngine\TypeSystem\Type\SlotType\SlotType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use Parsica\Parsica\Internal\Position;
use Parsica\Parsica\StringStream;
use PHPUnit\Framework\TestCase;

final class PhpTranspilerIntegrationTest extends TestCase
{
    /**
     * @return array<string,mixed>
     */
    public function transpilerExamples(): array
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
        $fileName = __DIR__ . '/Examples/' . $example . '/' . $example . '.afx';
        $stream = new StringStream(
            file_get_contents($fileName) ?: throw new \RuntimeException('could not load file.'),
            Position::initial($fileName)
        );
        $module = ModuleParser::parseFromStream($stream);

        $expected = file_get_contents(__DIR__ . '/Examples/' . $example . '/' . $example . '.php');

        $transpiler = new ModuleTranspiler(
            loader: new ModuleFileLoader(),
            // Add some assumed types to the global scope
            globalScope: new DummyScope([
                'ButtonType' => EnumStaticType::fromEnumDeclarationNode(
                    EnumDeclarationNode::fromString(
                        'enum ButtonType { LINK BUTTON SUBMIT NONE }'
                    )
                )
            ], [
                'string' => StringType::get(),
                'slot' => SlotType::get(),
                'number' => NumberType::get(),
                'boolean' => BooleanType::get(),
                'ButtonType' => EnumType::fromEnumDeclarationNode(
                    EnumDeclarationNode::fromString(
                        'enum ButtonType { LINK BUTTON SUBMIT NONE }'
                    )
                )
            ]),
            strategy: new ModuleTestStrategy()
        );

        $this->assertEquals($expected, $transpiler->transpile($module));
    }
}
