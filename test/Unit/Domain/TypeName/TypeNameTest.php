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

namespace PackageFactory\ComponentEngine\Test\Unit\Domain\TypeName;

use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\Domain\VariableName\VariableName;
use PHPUnit\Framework\TestCase;

final class TypeNameTest extends TestCase
{
    /**
     * @test
     */
    public function isFlyweight(): void
    {
        $this->assertSame(TypeName::from('Foo'), TypeName::from('Foo'));
        $this->assertSame(TypeName::from('Bar'), TypeName::from('Bar'));
        $this->assertSame(TypeName::from('FooBar'), TypeName::from('FooBar'));

        $this->assertNotSame(TypeName::from('Foo'), TypeName::from('Bar'));
   }

    /**
     * @test
     */
    public function convertsToVariableName(): void
    {
        $this->assertEquals(
            VariableName::from('Foo'),
            TypeName::from('Foo')->toVariableName()
        );
    }
}
