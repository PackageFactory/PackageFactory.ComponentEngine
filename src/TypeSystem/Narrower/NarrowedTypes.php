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

namespace PackageFactory\ComponentEngine\TypeSystem\Narrower;

use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

class NarrowedTypes
{
    /**
     * Map of identifierName to the corresponding inferred type
     * @var array<string,TypeInterface>
     */
    private readonly array $types;

    private function __construct(
        TypeInterface ...$types
    ) {
        /** @var array<string,TypeInterface> $types */
        $this->types = $types;
    }

    public static function empty(): self
    {
        return new self();
    }

    public static function fromEntry(string $identifierName, TypeInterface $type): self
    {
        return new self(...[$identifierName => $type]);
    }

    public function getType(string $identifierName): ?TypeInterface
    {
        return $this->types[$identifierName] ?? null;
    }
}
