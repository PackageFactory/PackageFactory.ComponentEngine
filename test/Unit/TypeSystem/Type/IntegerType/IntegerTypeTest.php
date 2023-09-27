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

namespace PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Type\IntegerType;

use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\TypeSystem\Type\IntegerType\IntegerType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PHPUnit\Framework\TestCase;

final class IntegerTypeTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function providesItsTypeName(): void
    {
        $this->assertEquals(TypeName::from('number'), IntegerType::singleton()->getName());
    }

    /**
     * @test
     */
    public function integerTypeIsSingleton(): void
    {
        $this->assertSame(IntegerType::singleton(), IntegerType::singleton());
    }

    /**
     * @test
     */
    public function isReturnsTrueIfGivenTypeIsIntegerType(): void
    {
        $this->assertTrue(IntegerType::singleton()->is(IntegerType::singleton()));
    }

    /**
     * @test
     */
    public function isReturnsFalseIfGivenTypeIsNotIntegerType(): void
    {
        $this->assertFalse(IntegerType::singleton()->is(StringType::singleton()));
    }
}
