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

namespace PackageFactory\ComponentEngine\Test\Unit\Target\Php\TargetSpecific;

use PackageFactory\ComponentEngine\Target\Php\TargetSpecific\ClassName;
use PHPUnit\Framework\TestCase;

final class ClassNameTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function isFlyweight(): void
    {
        $this->assertSame(
            ClassName::fromString('Vendor\\Site\\SomeClass'),
            ClassName::fromString('Vendor\\Site\\SomeClass')
        );
        $this->assertSame(
            ClassName::fromString('Vendor\\Site\\SomeOtherClass'),
            ClassName::fromString('Vendor\\Site\\SomeOtherClass')
        );
        $this->assertSame(
            ClassName::fromString('OtherVendor\\OtherSite\\SomeClass'),
            ClassName::fromString('OtherVendor\\OtherSite\\SomeClass')
        );
    }

    /**
     * @test
     * @return void
     */
    public function ensuresValidClassName(): void
    {
        // @TODO
    }

    /**
     * @test
     * @return void
     */
    public function providesFullyQualifiedClassName(): void
    {
        $className = ClassName::fromString('Vendor\\Site\\SomeClass');
        $this->assertEquals(
            'Vendor\\Site\\SomeClass',
            $className->getFullyQualifiedClassName()
        );
    }

    /**
     * @test
     * @return void
     */
    public function providesNamespace(): void
    {
        $className = ClassName::fromString('Vendor\\Site\\SomeClass');
        $this->assertEquals(
            'Vendor\\Site',
            $className->getNamespace()
        );
    }

    /**
     * @test
     * @return void
     */
    public function providesShortClassName(): void
    {
        $className = ClassName::fromString('Vendor\\Site\\SomeClass');
        $this->assertEquals(
            'SomeClass',
            $className->getShortClassName()
        );
    }
}