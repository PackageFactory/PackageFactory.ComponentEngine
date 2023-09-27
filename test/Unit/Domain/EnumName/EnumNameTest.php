<?php

/**
 * PackageFactory.ComponentEngine - Universal View Components for PHP
 *   Copyright (C) 2023 Contributors of PackageFactory.ComponentEngine
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

namespace PackageFactory\ComponentEngine\Test\Unit\Domain\EnumName;

use PackageFactory\ComponentEngine\Domain\EnumName\EnumName;
use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PHPUnit\Framework\TestCase;

final class EnumNameTest extends TestCase
{
    /**
     * @test
     */
    public function isFlyweight(): void
    {
        $this->assertSame(EnumName::from('Foo'), EnumName::from('Foo'));
        $this->assertSame(EnumName::from('Bar'), EnumName::from('Bar'));
        $this->assertSame(EnumName::from('FooBar'), EnumName::from('FooBar'));

        $this->assertNotSame(EnumName::from('Foo'), EnumName::from('Bar'));
   }

    /**
     * @test
     */
    public function convertsToTypeName(): void
    {
        $this->assertEquals(
            TypeName::from('Foo'),
            EnumName::from('Foo')->toTypeName()
        );
    }
}
