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

namespace PackageFactory\ComponentEngine\TypeResolver\Scope;

use PackageFactory\ComponentEngine\Type\Type;

final class ChainedScope extends Scope
{
    private function __construct(
        private readonly Scope $parent,
        private readonly Scope $child
    ) {
    }

    public static function from(Scope $parent, Scope $child): self
    {
        return new self($parent, $child);
    }

    public function lookupTypeOfValue(string $name): ?Type
    {
        return $this->child->lookupTypeOfValue($name) ?? $this->parent->lookupTypeOfValue($name);
    }

    public function lookupType(string $name): ?Type
    {
        return $this->child->lookupType($name) ?? $this->parent->lookupType($name);
    }
}