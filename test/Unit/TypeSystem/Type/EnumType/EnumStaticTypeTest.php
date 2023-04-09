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

final class EnumStaticTypeTest extends TestCase
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
        $enumStaticType = EnumStaticType::fromModuleIdAndDeclaration(
            ModuleId::fromString("module-a"),
            $enumDeclarationNode
        );

        $this->assertInstanceOf(EnumStaticType::class, $enumStaticType);
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
        $enumStaticType = EnumStaticType::fromModuleIdAndDeclaration(
            ModuleId::fromString("module-a"),
            $enumDeclarationNode
        );

        $this->assertEquals('SomeEnum', $enumStaticType->enumName);
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
     */
    public function providesMemberType(): void
    {
        $enumStaticType = EnumStaticType::fromModuleIdAndDeclaration(
            ModuleId::fromString("module-a"),
            EnumDeclarationNode::fromString(
                'enum SomeEnum { A B C }'
            )
        );

        $enumMemberType = $enumStaticType->getMemberType('A');
        $this->assertInstanceOf(EnumInstanceType::class, $enumMemberType);

        $this->assertSame($enumStaticType, $enumMemberType->enumStaticType);
        $this->assertSame('A', $enumMemberType->getMemberName());
    }

    /**
     * @test
     */
    public function canOnlyAccessValidMemberType(): void
    {
        $this->expectExceptionMessage('@TODO cannot access member NonExistent of enum SomeEnum');
        
        $enumStaticType = EnumStaticType::fromModuleIdAndDeclaration(
            ModuleId::fromString("module-a"),
            EnumDeclarationNode::fromString(
                'enum SomeEnum { A B C }'
            )
        );

        $enumStaticType->getMemberType('NonExistent');
    }

    /**
     * @test
     * @return void
     */
    public function canBeTransformedIntoInstanceType(): void
    {
        $enumDeclarationNode = EnumDeclarationNode::fromString(
            'enum SomeEnum { A }'
        );
        $enumStaticType = EnumStaticType::fromModuleIdAndDeclaration(
            ModuleId::fromString("module-a"),
            $enumDeclarationNode
        );

        $enumInstanceType = $enumStaticType->toEnumInstanceType();

        $this->assertInstanceOf(EnumInstanceType::class, $enumInstanceType);

        $this->assertInstanceOf(EnumStaticType::class, $enumInstanceType->enumStaticType);
        
        $this->assertTrue($enumInstanceType->isUnspecified());
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
        $enumStaticType = EnumStaticType::fromModuleIdAndDeclaration(
            ModuleId::fromString("module-a"),
            $enumDeclarationNode
        );

        $this->assertTrue($enumStaticType->is($enumStaticType));

        $enumStaticType2 = EnumStaticType::fromModuleIdAndDeclaration(
            ModuleId::fromString("module-a"),
            $enumDeclarationNode
        );

        $this->assertTrue($enumStaticType->is($enumStaticType2));
        $this->assertTrue($enumStaticType2->is($enumStaticType));
    }
}
