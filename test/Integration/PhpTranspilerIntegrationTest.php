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
use PackageFactory\ComponentEngine\Parser\Ast\ModuleNode;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\Transpiler\Php\Module\ModuleTranspiler;
use PackageFactory\ComponentEngine\TypeSystem\Type\BooleanType\BooleanType;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumStaticType;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumType;
use PackageFactory\ComponentEngine\TypeSystem\Type\NumberType\NumberType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PHPUnit\Framework\TestCase;

final class PhpTranspilerIntegrationTest extends TestCase
{
    public function transpilerExamples(): array
    {
        return [
            'Component' => ["Component"],
            'ComponentWithKeywords' => ["ComponentWithKeywords"],
            'ComponentWithNesting' => ["ComponentWithNesting"],
            'Enum' => ["Enum"],
            'Expression' => ["Expression"],
            'ImportExport' => ["ImportExport"],
            'Match' => ["Match"],
            'Numbers' => ["Numbers"],
            'Struct' => ["Struct"],
            'TemplateLiteral' => ["TemplateLiteral"],
        ];
    }

    /**
     * @dataProvider transpilerExamples
     * @test
     * @small
     * @param string $input
     * @return void
     */
    public function testTranspiler(string $example): void
    {
        $source = Source::fromFile(__DIR__ . '/Examples/' . $example . '/' . $example . '.afx');
        $tokenizer = Tokenizer::fromSource($source);
        $module = ModuleNode::fromTokens($tokenizer->getIterator());

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
                'slot' => StringType::get(),
                'number' => NumberType::get(),
                'boolean' => BooleanType::get(),
                'ButtonType' => EnumType::fromEnumDeclarationNode(
                    EnumDeclarationNode::fromString(
                        'enum ButtonType { LINK BUTTON SUBMIT NONE }'
                    )
                )
            ])
        );

        $this->assertEquals($expected, $transpiler->transpile($module));
    }
}
