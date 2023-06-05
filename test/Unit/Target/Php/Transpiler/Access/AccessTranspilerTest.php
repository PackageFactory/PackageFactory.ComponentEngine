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

use PackageFactory\ComponentEngine\Parser\Ast\AccessNode;
use PackageFactory\ComponentEngine\Parser\Ast\EnumDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\StructDeclarationNode;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\Access\AccessTranspiler;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumStaticType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StructType\StructType;
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
            scope: new DummyScope([
                'a' => StructType::fromStructDeclarationNode(
                    StructDeclarationNode::fromString(
                        'struct A { b: B }'
                    )
                ),
                'SomeEnum' => EnumStaticType::fromEnumDeclarationNode(
                    EnumDeclarationNode::fromString(
                        'enum SomeEnum { A B C }'
                    )
                ),
                'someStruct' => StructType::fromStructDeclarationNode(
                    StructDeclarationNode::fromString(<<<'AFX'
                        struct SomeStruct {
                            foo: string
                            deep: ?SomeStruct
                        }
                        AFX
                    )
                )
            ])
        );
        $accessNode = ExpressionNode::fromString($accessAsString)->root;
        assert($accessNode instanceof AccessNode);

        $actualTranspilationResult = $accessTranspiler->transpile(
            $accessNode
        );

        $this->assertEquals(
            $expectedTranspilationResult,
            $actualTranspilationResult
        );
    }
}
