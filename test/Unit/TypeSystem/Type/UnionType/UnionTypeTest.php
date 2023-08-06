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

namespace PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Type\UnionType;

use PackageFactory\ComponentEngine\TypeSystem\Type\BooleanType\BooleanType;
use PackageFactory\ComponentEngine\TypeSystem\Type\IntegerType\IntegerType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PackageFactory\ComponentEngine\TypeSystem\Type\UnionType\UnionType;
use PHPUnit\Framework\TestCase;

final class UnionTypeTest extends TestCase
{
    /**
     * @test
     */
    public function staticOfResolvesToGivenTypeIfOnlyOneTypeIsGiven(): void
    {
        $unionType = UnionType::of(StringType::singleton());
        $this->assertTrue($unionType->is(StringType::singleton()));
        $this->assertTrue(StringType::singleton()->is($unionType));

        $unionType = UnionType::of(IntegerType::singleton());
        $this->assertTrue($unionType->is(IntegerType::singleton()));
        $this->assertTrue(IntegerType::singleton()->is($unionType));
    }

    /**
     * @test
     */
    public function staticOfResolvesToGivenTypeIfAllGivenTypesAreIdentical(): void
    {
        $unionType = UnionType::of(StringType::singleton(), StringType::singleton(), StringType::singleton());
        $this->assertTrue($unionType->is(StringType::singleton()));
        $this->assertTrue(StringType::singleton()->is($unionType));
    }

    /**
     * @test
     */
    public function isReturnsTrueIfGivenTypeIsCongruentUnionType(): void
    {
        $unionType = UnionType::of(StringType::singleton(), IntegerType::singleton());
        $otherUnionType = UnionType::of(IntegerType::singleton(), StringType::singleton());

        $this->assertTrue($unionType->is($otherUnionType));
    }

    /**
     * @test
     */
    public function isReturnsTrueIfGivenTypeIsCongruentUnionTypeWithRedundantMembers(): void
    {
        $unionType = UnionType::of(StringType::singleton(), IntegerType::singleton());
        $otherUnionType = UnionType::of(IntegerType::singleton(), StringType::singleton(), IntegerType::singleton(), StringType::singleton());

        $this->assertTrue($unionType->is($otherUnionType));
    }

    /**
     * @test
     */
    public function isReturnsFalseIfGivenTypeIsNotAUnionType(): void
    {
        $unionType = UnionType::of(StringType::singleton(), IntegerType::singleton());

        $this->assertFalse($unionType->is(IntegerType::singleton()));
        $this->assertFalse($unionType->is(StringType::singleton()));
    }

    /**
     * @test
     */
    public function isReturnsFalseIfGivenTypeIsANonCongruentUnionType(): void
    {
        $unionType = UnionType::of(StringType::singleton(), IntegerType::singleton());
        $otherUnionType = UnionType::of(StringType::singleton(), BooleanType::singleton());

        $this->assertFalse($unionType->is($otherUnionType));
    }
}
