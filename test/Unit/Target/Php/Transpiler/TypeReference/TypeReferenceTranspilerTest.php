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

namespace PackageFactory\ComponentEngine\Test\Unit\Target\Php\Transpiler\TypeReference;

use PackageFactory\ComponentEngine\Module\ModuleId;
use PackageFactory\ComponentEngine\Parser\Ast\ComponentDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\EnumDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\StructDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\TypeReferenceNode;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\TypeReference\TypeReferenceTranspiler;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\TypeSystem\Type\BooleanType\BooleanType;
use PackageFactory\ComponentEngine\TypeSystem\Type\ComponentType\ComponentType;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumStaticType;
use PackageFactory\ComponentEngine\TypeSystem\Type\NumberType\NumberType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StructType\StructType;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;
use PHPUnit\Framework\TestCase;

final class TypeReferenceTranspilerTest extends TestCase
{
    protected function getTypeReferenceTranspiler(): TypeReferenceTranspiler
    {
        return new TypeReferenceTranspiler(
            scope: new DummyScope([], [
                'string' => StringType::get(),
                'boolean' => BooleanType::get(),
                'number' => NumberType::get(),
                'Button' => ComponentType::fromComponentDeclarationNode(
                    ComponentDeclarationNode::fromString('component Button { return "" }')
                ),
                'DayOfWeek' => EnumStaticType::fromModuleIdAndDeclaration(
                    ModuleId::fromString("module-a"),
                    EnumDeclarationNode::fromString('enum DayOfWeek {}')
                ),
                'Link' => StructType::fromStructDeclarationNode(
                    StructDeclarationNode::fromString('struct Link {}')
                ),
                'SomeType' => $this->createMock(TypeInterface::class)
            ]),
            strategy: new TypeReferenceTestStrategy()
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function primitiveTypeReferenceExamples(): array
    {
        return [
            'string' => ['string', 'string'],
            'boolean' => ['boolean', 'bool'],
            'number' => ['number', 'int|float'],
            'Component' => ['Button', 'ButtonComponent'],
            'Enum' => ['DayOfWeek', 'DayOfWeekEnum'],
            'Struct' => ['Link', 'LinkStruct'],
            'Custom' => ['SomeType', 'SomeTypeCustom'],
        ];
    }

    /**
     * @dataProvider primitiveTypeReferenceExamples
     * @test
     * @param string $typeReferenceAsString
     * @param string $expectedTranspilationResult
     * @return void
     */
    public function transpilesReferencesToPrimitiveTypes(string $typeReferenceAsString, string $expectedTranspilationResult): void
    {
        $typeReferenceTranspiler = $this->getTypeReferenceTranspiler();
        $typeReferenceNode = TypeReferenceNode::fromString($typeReferenceAsString);

        $actualTranspilationResult = $typeReferenceTranspiler->transpile(
            $typeReferenceNode
        );

        $this->assertEquals(
            $expectedTranspilationResult,
            $actualTranspilationResult
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function optionalTypeReferenceExamples(): array
    {
        return [
            'string' => ['?string', '?string'],
            'boolean' => ['?boolean', '?bool'],
            'number' => ['?number', 'null|int|float'],
            'Component' => ['?Button', '?ButtonComponent'],
            'Enum' => ['?DayOfWeek', '?DayOfWeekEnum'],
            'Struct' => ['?Link', '?LinkStruct'],
            'Custom' => ['?SomeType', '?SomeTypeCustom'],
        ];
    }

    /**
     * @dataProvider optionalTypeReferenceExamples
     * @test
     * @param string $typeReferenceAsString
     * @param string $expectedTranspilationResult
     * @return void
     */
    public function transpilesReferencesToOptionalTypes(string $typeReferenceAsString, string $expectedTranspilationResult): void
    {
        $typeReferenceTranspiler = $this->getTypeReferenceTranspiler();
        $typeReferenceNode = TypeReferenceNode::fromString($typeReferenceAsString);

        $actualTranspilationResult = $typeReferenceTranspiler->transpile(
            $typeReferenceNode
        );

        $this->assertEquals(
            $expectedTranspilationResult,
            $actualTranspilationResult
        );
    }
}
