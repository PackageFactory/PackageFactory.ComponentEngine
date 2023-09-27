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

use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\Domain\VariableName\VariableName;
use PackageFactory\ComponentEngine\TypeSystem\AtomicTypeInterface;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

final class DummyScope implements ScopeInterface
{
    /**
     * @var array<string,AtomicTypeInterface>
     */
    private readonly array $typeNameToTypeMap;

    /**
     * @param AtomicTypeInterface[] $knownTypes
     * @param array<string,TypeInterface> $identifierToTypeReferenceMap
     */
    public function __construct(
        array $knownTypes = [],
        private readonly array $identifierToTypeReferenceMap = [],
    ) {
        $typeNameToTypeMap = [];
        foreach ($knownTypes as $type) {
            /** @var AtomicTypeInterface $type */
            $typeNameToTypeMap[$type->getName()->value] = $type;
        }

        $this->typeNameToTypeMap = $typeNameToTypeMap;
    }

    public function getType(TypeName $typeName): AtomicTypeInterface
    {
        if ($type = $this->typeNameToTypeMap[$typeName->value] ?? null) {
            return $type;
        }

        throw new \Exception('DummyScope: Unknown type ' . $typeName->value);
    }

    public function getTypeOf(VariableName $variableName): ?TypeInterface
    {
        return $this->identifierToTypeReferenceMap[$variableName->value] ?? null;
    }
}
