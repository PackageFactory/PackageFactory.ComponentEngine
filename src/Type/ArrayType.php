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

namespace PackageFactory\ComponentEngine\Type;

use PackageFactory\ComponentEngine\Type\Std\ArrayStdApi;

final class ArrayType extends Type
{
    private function __construct(public readonly Type $itemType)
    {
    }

    public static function of(Type $itemType): self
    {
        return new self($itemType);
    }

    public function access(string $key): Type
    {
        return ArrayStdApi::for($this)->access($key);
    }

    public function __toString(): string
    {
        return sprintf('%s[]', (string) $this->itemType);
    }
}
