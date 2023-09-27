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

use PackageFactory\ComponentEngine\Domain\StructName\StructName;
use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\Module\ModuleId;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\TypeReference\TypeReferenceTranspiler;
use PackageFactory\ComponentEngine\Test\Unit\Language\ASTNodeFixtures;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\TypeSystem\AtomicTypeInterface;
use PackageFactory\ComponentEngine\TypeSystem\Type\BooleanType\BooleanType;
use PackageFactory\ComponentEngine\TypeSystem\Type\ComponentType\ComponentType;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumStaticType;
use PackageFactory\ComponentEngine\TypeSystem\Type\IntegerType\IntegerType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StructType\Properties;
use PackageFactory\ComponentEngine\TypeSystem\Type\StructType\StructType;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;
use PHPUnit\Framework\TestCase;

final class TypeReferenceTranspilerTest extends TestCase
{
    private function mockAtomicType(string $name): AtomicTypeInterface
    {
        return new class($name) implements AtomicTypeInterface
        {
            public function __construct(private readonly string $name)
            {
            }

            public function getName(): TypeName
            {
                return TypeName::from($this->name);
            }

            public function is(TypeInterface $other): bool
            {
                return $other === $this;
            }
        };
    }

    protected function getTypeReferenceTranspiler(): TypeReferenceTranspiler
    {
        return new TypeReferenceTranspiler(
            scope: new DummyScope([
                StringType::singleton(),
                BooleanType::singleton(),
                IntegerType::singleton(),
                ComponentType::fromComponentDeclarationNode(
                    ASTNodeFixtures::ComponentDeclaration('component Button { return "" }')
                ),
                EnumStaticType::fromModuleIdAndDeclaration(
                    ModuleId::fromString("module-a"),
                    ASTNodeFixtures::EnumDeclaration('enum DayOfWeek {}')
                ),
                new StructType(
                    name: StructName::from('Link'),
                    properties: new Properties()
                ),
                $this->mockAtomicType('SomeType')
            ]),
            strategy: new TypeReferenceTestStrategy()
        );
    }

    /**
     * @return array<string,mixed>
     */
    public static function primitiveTypeReferenceExamples(): array
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
        $typeReferenceNode = ASTNodeFixtures::TypeReference($typeReferenceAsString);

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
    public static function optionalTypeReferenceExamples(): array
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
        $typeReferenceNode = ASTNodeFixtures::TypeReference($typeReferenceAsString);

        $actualTranspilationResult = $typeReferenceTranspiler->transpile(
            $typeReferenceNode
        );

        $this->assertEquals(
            $expectedTranspilationResult,
            $actualTranspilationResult
        );
    }
}
