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

namespace PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Type\StructType;

use PackageFactory\ComponentEngine\Domain\PropertyName\PropertyName;
use PackageFactory\ComponentEngine\Domain\StructName\StructName;
use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\Domain\TypeName\TypeNames;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StructType\Properties;
use PackageFactory\ComponentEngine\TypeSystem\Type\StructType\Property;
use PackageFactory\ComponentEngine\TypeSystem\Type\StructType\StructType;
use PackageFactory\ComponentEngine\TypeSystem\TypeReference;
use PHPUnit\Framework\TestCase;

final class StructTypeTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function providesNameOfTheStruct(): void
    {
        $structType = new StructType(
            name: StructName::from('SomeStruct'),
            properties: new Properties()
        );

        $this->assertEquals(
            TypeName::from('SomeStruct'),
            $structType->getName()
        );
    }

    /**
     * @test
     * @return void
     */
    public function isEquivalentToItself(): void
    {
        $structType = new StructType(
            name: StructName::from('SomeStruct'),
            properties: new Properties()
        );

        $this->assertTrue($structType->is($structType));
    }

    /**
     * @test
     * @return void
     */
    public function providesTypeOfProperty(): void
    {
        $structType = new StructType(
            name: StructName::from('SomeStruct'),
            properties: new Properties(
                new Property(
                    name: PropertyName::from('foo'),
                    type: $typeOfFoo = new TypeReference(
                        names: new TypeNames(TypeName::from('string')),
                        isOptional: false,
                        isArray: false
                    )
                )
            )
        );

        $this->assertEquals(
            $typeOfFoo,
            $structType->getTypeOfProperty(PropertyName::from('foo'))
        );
    }
}
