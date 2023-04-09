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

namespace PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Type\EnumType;

use PackageFactory\ComponentEngine\Module\ModuleId;
use PackageFactory\ComponentEngine\Parser\Ast\EnumDeclarationNode;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumInstanceType;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumStaticType;
use PHPUnit\Framework\TestCase;

final class EnumInstanceTypeTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function canBeCreatedFromEnumDeclarationNode(): void
    {
        $enumDeclarationNode = EnumDeclarationNode::fromString(
            'enum Foo { BAR BAZ }'
        );
        $enumType = EnumInstanceType::fromModuleIdAndDeclaration(
            ModuleId::fromString("module-a"),
            $enumDeclarationNode
        );

        $this->assertInstanceOf(EnumInstanceType::class, $enumType);
    }

    /**
     * @test
     * @return void
     */
    public function providesNameOfTheEnum(): void
    {
        $enumDeclarationNode = EnumDeclarationNode::fromString(
            'enum SomeEnum {}'
        );
        $enumType = EnumInstanceType::fromModuleIdAndDeclaration(
            ModuleId::fromString("module-a"),
            $enumDeclarationNode
        );

        $this->assertEquals('SomeEnum', $enumType->enumName);
    }

    /**
     * @test
     */
    public function providesMemberNames(): void
    {
        $enumStaticType = EnumStaticType::fromModuleIdAndDeclaration(
            ModuleId::fromString("module-a"),
            EnumDeclarationNode::fromString(
                'enum SomeEnum { A B C }'
            )
        );

        $this->assertSame(["A", "B", "C"], $enumStaticType->getMemberNames());
    }

    /**
     * @test
     * @return void
     */
    public function canBeComparedToOther(): void
    {
        $enumDeclarationNode = EnumDeclarationNode::fromString(
            'enum SomeEnum { A }'
        );
        $enumInstanceType = EnumInstanceType::fromModuleIdAndDeclaration(
            ModuleId::fromString("module-a"),
            $enumDeclarationNode
        );

        $this->assertTrue($enumInstanceType->is($enumInstanceType));

        $enumStaticType = EnumStaticType::fromModuleIdAndDeclaration(
            ModuleId::fromString("module-a"),
            $enumDeclarationNode
        );

        $this->assertTrue($enumInstanceType->is($enumStaticType));
    }
}
