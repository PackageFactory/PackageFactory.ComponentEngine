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

use PackageFactory\ComponentEngine\Parser\Ast\ComponentDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\TypeReferenceNode;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\Type\BooleanType\BooleanType;
use PackageFactory\ComponentEngine\TypeSystem\Type\NumberType\NumberType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

final class ComponentScope implements ScopeInterface
{
    public function __construct(
        private readonly ComponentDeclarationNode $componentDeclarationNode,
        private readonly ScopeInterface $parentScope
    ) {
    }

    public function lookupTypeFor(string $name): ?TypeInterface
    {
        $propertyDeclarationNode = $this->componentDeclarationNode->propertyDeclarations->getPropertyDeclarationNodeOfName($name);
        if ($propertyDeclarationNode) {
            $typeReferenceNode = $propertyDeclarationNode->type;
            return $this->resolveTypeReference($typeReferenceNode);
        }

        return $this->parentScope->lookupTypeFor($name);
    }

    public function resolveTypeReference(TypeReferenceNode $typeReferenceNode): TypeInterface
    {
        return $this->parentScope->resolveTypeReference($typeReferenceNode);
    }
}
