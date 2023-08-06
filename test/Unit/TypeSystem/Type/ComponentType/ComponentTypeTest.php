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

namespace PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Type\ComponentType;

use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\Test\Unit\Language\ASTNodeFixtures;
use PackageFactory\ComponentEngine\TypeSystem\Type\ComponentType\ComponentType;
use PHPUnit\Framework\TestCase;

final class ComponentTypeTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function canBeCreatedFromComponentDeclarationNode(): void
    {
        $componentDeclarationNode = ASTNodeFixtures::ComponentDeclaration(
            'component Foo { a: string b: number return <div>{a} and {b}</div> }'
        );
        $componentType = ComponentType::fromComponentDeclarationNode(
            $componentDeclarationNode
        );

        $this->assertInstanceOf(ComponentType::class, $componentType);
    }

    /**
     * @test
     * @return void
     */
    public function providesNameOfTheComponent(): void
    {
        $componentDeclarationNode = ASTNodeFixtures::ComponentDeclaration(
            'component SomeComponent { return "" }'
        );
        $componentType = ComponentType::fromComponentDeclarationNode(
            $componentDeclarationNode
        );

        $this->assertEquals(
            TypeName::from('SomeComponent'),
            $componentType->getName()
        );
    }

    /**
     * @test
     * @return void
     */
    public function isEquivalentToItself(): void
    {
        $componentDeclarationNode = ASTNodeFixtures::ComponentDeclaration(
            'component SomeComponent { return "" }'
        );
        $componentType = ComponentType::fromComponentDeclarationNode(
            $componentDeclarationNode
        );

        $this->assertTrue($componentType->is($componentType));
    }
}
