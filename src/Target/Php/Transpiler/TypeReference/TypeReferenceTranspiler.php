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
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\Type\BooleanType\BooleanType;
use PackageFactory\ComponentEngine\TypeSystem\Type\ComponentType\ComponentType;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumType;
use PackageFactory\ComponentEngine\TypeSystem\Type\NumberType\NumberType;
use PackageFactory\ComponentEngine\TypeSystem\Type\SlotType\SlotType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StructType\StructType;

final class TypeReferenceTranspiler
{
    public function __construct(
        private readonly ScopeInterface $scope,
        private readonly TypeReferenceStrategyInterface $strategy
    ) {
    }

    public function transpile(TypeReferenceNode $typeReferenceNode): string
    {
        $type = $this->scope->resolveTypeReference($typeReferenceNode);

        return match ($type::class) {
            NumberType::class => 'int|float',
            StringType::class => 'string',
            BooleanType::class => 'bool',
            SlotType::class => $this->strategy->getPhpTypeReferenceForSlotType($type, $typeReferenceNode),
            ComponentType::class => $this->strategy->getPhpTypeReferenceForComponentType($type, $typeReferenceNode),
            EnumType::class => $this->strategy->getPhpTypeReferenceForEnumType($type, $typeReferenceNode),
            StructType::class => $this->strategy->getPhpTypeReferenceForStructType($type, $typeReferenceNode),
            default => $this->strategy->getPhpTypeReferenceForCustomType($type, $typeReferenceNode)
        };
    }
}
