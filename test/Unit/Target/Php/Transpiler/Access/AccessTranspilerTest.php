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

namespace PackageFactory\ComponentEngine\Test\Unit\Target\Php\Transpiler\Access;

use PackageFactory\ComponentEngine\Domain\PropertyName\PropertyName;
use PackageFactory\ComponentEngine\Domain\StructName\StructName;
use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\Domain\TypeName\TypeNames;
use PackageFactory\ComponentEngine\Module\ModuleId;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\Access\AccessTranspiler;
use PackageFactory\ComponentEngine\Test\Unit\Language\ASTNodeFixtures;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumStaticType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StructType\Properties;
use PackageFactory\ComponentEngine\TypeSystem\Type\StructType\Property;
use PackageFactory\ComponentEngine\TypeSystem\Type\StructType\StructType;
use PackageFactory\ComponentEngine\TypeSystem\TypeReference;
use PHPUnit\Framework\TestCase;

final class AccessTranspilerTest extends TestCase
{
    /**
     * @return array<string,mixed>
     */
    public static function accessExamples(): array
    {
        return [
            'a.b' => ['a.b', '$this->a->b'],
            'a.b.c' => ['a.b.c', '$this->a->b->c'],
            'SomeEnum.A' => ['SomeEnum.A', 'SomeEnum::A'],
            'someStruct.foo' => ['someStruct.foo', '$this->someStruct->foo'],
            'someStruct?.foo' => ['someStruct?.foo', '$this->someStruct?->foo'],
            'someStruct.deep?.foo' => ['someStruct.deep?.foo', '$this->someStruct->deep?->foo']
        ];
    }

    /**
     * @dataProvider accessExamples
     * @test
     * @param string $accessAsString
     * @param string $expectedTranspilationResult
     * @return void
     */
    public function transpilesAccessNodes(string $accessAsString, string $expectedTranspilationResult): void
    {
        $accessTranspiler = new AccessTranspiler(
            scope: new DummyScope(
                [
                    $structTypeA = new StructType(
                        name: StructName::from('A'),
                        properties: new Properties(
                            new Property(
                                name: PropertyName::from('b'),
                                type: new TypeReference(
                                    names: new TypeNames(TypeName::from('B')),
                                    isOptional: false,
                                    isArray: false
                                )
                            )
                        )
                    ),
                    $enumStaticType = EnumStaticType::fromModuleIdAndDeclaration(
                        ModuleId::fromString("module-a"),
                        ASTNodeFixtures::EnumDeclaration(
                            'enum SomeEnum { A B C }'
                        )
                    ),
                    $structTypeSomeStruct = new StructType(
                        name: StructName::from('SomeStruct'),
                        properties: new Properties(
                            new Property(
                                name: PropertyName::from('b'),
                                type: new TypeReference(
                                    names: new TypeNames(TypeName::from('foo')),
                                    isOptional: false,
                                    isArray: false
                                )
                            ),
                            new Property(
                                name: PropertyName::from('deep'),
                                type: new TypeReference(
                                    names: new TypeNames(TypeName::from('SomeStruct')),
                                    isOptional: true,
                                    isArray: false
                                )
                            )
                        )
                    ),
                    new StructType(
                        name: StructName::from('B'),
                        properties: new Properties(
                            new Property(
                                name: PropertyName::from('c'),
                                type: new TypeReference(
                                    names: new TypeNames(TypeName::from('string')),
                                    isOptional: false,
                                    isArray: false
                                )
                            )
                        )
                    ),
                ],
                [
                    'a' => $structTypeA,
                    'SomeEnum' => $enumStaticType,
                    'someStruct' => $structTypeSomeStruct
                ]
            )
        );
        $accessNode = ASTNodeFixtures::Access($accessAsString);

        $actualTranspilationResult = $accessTranspiler->transpile(
            $accessNode
        );

        $this->assertEquals(
            $expectedTranspilationResult,
            $actualTranspilationResult
        );
    }
}
