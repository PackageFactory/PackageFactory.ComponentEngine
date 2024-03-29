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

namespace PackageFactory\ComponentEngine\TypeSystem\Scope\ComponentScope;

use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\Domain\VariableName\VariableName;
use PackageFactory\ComponentEngine\Language\AST\Node\ComponentDeclaration\ComponentDeclarationNode;
use PackageFactory\ComponentEngine\TypeSystem\AtomicTypeInterface;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumStaticType;
use PackageFactory\ComponentEngine\TypeSystem\Type\UnionType\UnionType;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

final class ComponentScope implements ScopeInterface
{
    public function __construct(
        private readonly ComponentDeclarationNode $componentDeclarationNode,
        private readonly ScopeInterface $parentScope
    ) {
    }

    public function getType(TypeName $typeName): AtomicTypeInterface
    {
        return $this->parentScope->getType($typeName);
    }

    public function getTypeOf(VariableName $variableName): ?TypeInterface
    {
        foreach ($this->componentDeclarationNode->props->items as $propertyDeclarationNode) {
            if ($propertyDeclarationNode->name->value->value === $variableName->value) {
                $types = [];
                foreach ($propertyDeclarationNode->type->names->toTypeNames()->items as $typeName) {
                    $type = $this->getType($typeName);
                    if ($type instanceof EnumStaticType) {
                        $type = $type->toEnumInstanceType();
                    }
                    $types[] = $type;
                }

                return UnionType::of(...$types);
            }
        }

        return $this->parentScope->getTypeOf($variableName);
    }
}
