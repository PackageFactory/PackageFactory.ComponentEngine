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

namespace PackageFactory\ComponentEngine\Target\Php\Transpiler\TypeReference;

use PackageFactory\ComponentEngine\Parser\Ast\TypeReferenceNode;
use PackageFactory\ComponentEngine\TypeSystem\Type\ComponentType\ComponentType;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumType;
use PackageFactory\ComponentEngine\TypeSystem\Type\SlotType\SlotType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StructType\StructType;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

interface TypeReferenceStrategyInterface
{
    public function getPhpTypeReferenceForSlotType(SlotType $slotType, TypeReferenceNode $typeReferenceNode): string;
    public function getPhpTypeReferenceForComponentType(ComponentType $componentType, TypeReferenceNode $typeReferenceNode): string;
    public function getPhpTypeReferenceForEnumType(EnumType $enumType, TypeReferenceNode $typeReferenceNode): string;
    public function getPhpTypeReferenceForStructType(StructType $structType, TypeReferenceNode $typeReferenceNode): string;
    public function getPhpTypeReferenceForCustomType(TypeInterface $customType, TypeReferenceNode $typeReferenceNode): string;
}
