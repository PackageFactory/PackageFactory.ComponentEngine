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

namespace PackageFactory\ComponentEngine\Test\Unit\Target\Php\Transpiler\TypeReference;

use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeReferenceNode;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\TypeReference\TypeReferenceStrategyInterface;
use PackageFactory\ComponentEngine\TypeSystem\AtomicTypeInterface;
use PackageFactory\ComponentEngine\TypeSystem\Type\ComponentType\ComponentType;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumStaticType;
use PackageFactory\ComponentEngine\TypeSystem\Type\SlotType\SlotType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StructType\StructType;

final class TypeReferenceTestStrategy implements TypeReferenceStrategyInterface
{
    public function getPhpTypeReferenceForSlotType(SlotType $slotType, TypeReferenceNode $typeReferenceNode): string
    {
        return 'SlotInterface';
    }

    public function getPhpTypeReferenceForComponentType(ComponentType $componentType, TypeReferenceNode $typeReferenceNode): string
    {
        return $componentType->getName()->value . 'Component';
    }

    public function getPhpTypeReferenceForEnumType(EnumStaticType $enumType, TypeReferenceNode $typeReferenceNode): string
    {
        return $enumType->getName()->value . 'Enum';
    }

    public function getPhpTypeReferenceForStructType(StructType $structType, TypeReferenceNode $typeReferenceNode): string
    {
        return $structType->getName()->value . 'Struct';
    }

    public function getPhpTypeReferenceForCustomType(AtomicTypeInterface $customType, TypeReferenceNode $typeReferenceNode): string
    {
        return $customType->getName()->value . 'Custom';
    }
}
