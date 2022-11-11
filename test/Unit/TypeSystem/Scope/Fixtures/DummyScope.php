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

namespace PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures;

use PackageFactory\ComponentEngine\Parser\Ast\TypeReferenceNode;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

final class DummyScope implements ScopeInterface
{
    /**
     * @param array<string,TypeInterface> $identifierToTypeMap
     * @param array<string,TypeInterface> $typeNameToTypeMap
     */
    public function __construct(
        private readonly array $identifierToTypeMap = [],
        private readonly array $typeNameToTypeMap = []
    ) {
    }

    public function lookupTypeFor(string $name): ?TypeInterface
    {
        return $this->identifierToTypeMap[$name] ?? null;
    }

    public function resolveTypeReference(TypeReferenceNode $typeReferenceNode): TypeInterface
    {
        if ($type = $this->typeNameToTypeMap[$typeReferenceNode->name] ?? null) {
            return $type;
        }

        throw new \Exception('DummyScope: Unknown type ' . $typeReferenceNode->name);
    }
}
