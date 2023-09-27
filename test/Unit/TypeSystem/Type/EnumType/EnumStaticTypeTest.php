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

use PackageFactory\ComponentEngine\Domain\EnumMemberName\EnumMemberName;
use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\Module\ModuleId;
use PackageFactory\ComponentEngine\Test\Unit\Language\ASTNodeFixtures;
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
        $enumDeclarationNode = ASTNodeFixtures::EnumDeclaration(
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
        $enumDeclarationNode = ASTNodeFixtures::EnumDeclaration(
            'enum SomeEnum {}'
        );
        $enumStaticType = EnumStaticType::fromModuleIdAndDeclaration(
            ModuleId::fromString("module-a"),
            $enumDeclarationNode
        );

        $this->assertEquals(
            TypeName::from('SomeEnum'),
            $enumStaticType->getName()
        );
    }

    /**
     * @test
     */
    public function providesMemberNames(): void
    {
        $enumStaticType = EnumStaticType::fromModuleIdAndDeclaration(
            ModuleId::fromString("module-a"),
            ASTNodeFixtures::EnumDeclaration(
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
            ASTNodeFixtures::EnumDeclaration(
                'enum SomeEnum { A B C }'
            )
        );

        $enumMemberType = $enumStaticType->getMemberType(EnumMemberName::from('A'));
        $this->assertInstanceOf(EnumInstanceType::class, $enumMemberType);

        $this->assertEquals($enumStaticType, $enumMemberType->enumStaticType);
        $this->assertEquals(EnumMemberName::from('A'), $enumMemberType->getMemberName());
    }

    /**
     * @test
     */
    public function canOnlyAccessValidMemberType(): void
    {
        $this->expectExceptionMessage('@TODO cannot access member NonExistent of enum SomeEnum');

        $enumStaticType = EnumStaticType::fromModuleIdAndDeclaration(
            ModuleId::fromString("module-a"),
            ASTNodeFixtures::EnumDeclaration(
                'enum SomeEnum { A B C }'
            )
        );

        $enumStaticType->getMemberType(EnumMemberName::from('NonExistent'));
    }

    /**
     * @test
     * @return void
     */
    public function canBeTransformedIntoInstanceType(): void
    {
        $enumDeclarationNode = ASTNodeFixtures::EnumDeclaration(
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
    public function isEquivalentToItself(): void
    {
        $enumDeclarationNode = ASTNodeFixtures::EnumDeclaration(
            'enum SomeEnum { A }'
        );
        $enumStaticType = EnumStaticType::fromModuleIdAndDeclaration(
            ModuleId::fromString("module-a"),
            $enumDeclarationNode
        );

        $this->assertTrue($enumStaticType->is($enumStaticType));
    }

    /**
     * @test
     * @return void
     */
    public function canBeComparedToOther(): void
    {
        $enumDeclarationNode1 = ASTNodeFixtures::EnumDeclaration(
            'enum SomeEnum { A }'
        );
        $enumStaticType1 = EnumStaticType::fromModuleIdAndDeclaration(
            ModuleId::fromString("module-a"),
            $enumDeclarationNode1
        );
        $enumDeclarationNode2 = ASTNodeFixtures::EnumDeclaration(
            'enum SomeEnum { A }'
        );
        $enumStaticType2 = EnumStaticType::fromModuleIdAndDeclaration(
            ModuleId::fromString("module-a"),
            $enumDeclarationNode2
        );

        $this->assertTrue($enumStaticType1->is($enumStaticType2));
        $this->assertTrue($enumStaticType2->is($enumStaticType1));
    }
}
