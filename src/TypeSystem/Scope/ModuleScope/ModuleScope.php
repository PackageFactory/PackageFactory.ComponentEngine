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

namespace PackageFactory\ComponentEngine\TypeSystem\Scope\ModuleScope;

use PackageFactory\ComponentEngine\Module\LoaderInterface;
use PackageFactory\ComponentEngine\Parser\Ast\ModuleNode;
use PackageFactory\ComponentEngine\Parser\Ast\TypeReferenceNode;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

final class ModuleScope implements ScopeInterface
{
    public function __construct(
        private readonly LoaderInterface $loader,
        private readonly ModuleNode $moduleNode,
        private readonly ?ScopeInterface $parentScope
    ) {
    }

    public function lookupTypeFor(string $name): ?TypeInterface
    {
        return $this->parentScope?->lookupTypeFor($name) ?? null;
    }

    public function resolveTypeReference(TypeReferenceNode $typeReferenceNode): TypeInterface
    {
        if ($importNode = $this->moduleNode->imports->get($typeReferenceNode->name)) {
            return $this->loader->resolveTypeOfImport($importNode);
        }

        if ($this->parentScope) {
            return $this->parentScope->resolveTypeReference($typeReferenceNode);
        }

        throw new \Exception('@TODO: Unknown Type ' . $typeReferenceNode->name);
    }
}