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

namespace PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Type\NullType;

use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\TypeSystem\Type\NullType\NullType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PHPUnit\Framework\TestCase;

final class NullTypeTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function providesItsTypeName(): void
    {
        $this->assertEquals(TypeName::from('null'), NullType::singleton()->getName());
    }

    /**
     * @test
     */
    public function nullTypeIsSingleton(): void
    {
        $this->assertSame(NullType::singleton(), NullType::singleton());
    }

    /**
     * @test
     */
    public function isReturnsTrueIfGivenTypeIsNullType(): void
    {
        $this->assertTrue(NullType::singleton()->is(NullType::singleton()));
    }

    /**
     * @test
     */
    public function isReturnsFalseIfGivenTypeIsNotNullType(): void
    {
        $this->assertFalse(NullType::singleton()->is(StringType::singleton()));
    }
}
