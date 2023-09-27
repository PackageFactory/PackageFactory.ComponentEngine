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

namespace PackageFactory\ComponentEngine\TypeSystem\Type\StructType;

use PackageFactory\ComponentEngine\Domain\PropertyName\PropertyName;
use PackageFactory\ComponentEngine\Domain\StructName\StructName;
use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\TypeSystem\AtomicTypeInterface;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;
use PackageFactory\ComponentEngine\TypeSystem\TypeReference;

final class StructType implements AtomicTypeInterface
{
    public function __construct(
        private readonly StructName $name,
        private readonly Properties $properties
    ) {
    }

    public function getName(): TypeName
    {
        return $this->name->toTypeName();
    }

    public function is(TypeInterface $other): bool
    {
        return $other === $this;
    }

    public function getTypeOfProperty(PropertyName $propertyName): TypeReference
    {
        if ($property = $this->properties->get($propertyName)) {
            return $property->type;
        }

        throw new \Exception('@TODO: Unknown struct property: ' . $propertyName->value);
    }
}
