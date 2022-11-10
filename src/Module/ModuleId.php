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

namespace PackageFactory\ComponentEngine\Module;

use PackageFactory\ComponentEngine\Parser\Source\Source;

final class ModuleId
{
    /**
     * @var array<string,ModuleId>
     */
    private static array $instances;

    private function __construct(private readonly string $value)
    {
    }

    public static function fromString(string $moduleIdAsString): self
    {
        return self::$instances[$moduleIdAsString] ??= new self($moduleIdAsString);
    }

    public static function fromSource(Source $source): self
    {
        if ($source->path->isMemory()) {
            return self::fromString(
                sprintf(
                    '%s#%s',
                    $source->path,
                    hash('sha256', $source->contents)
                )
            );
        } else {
            return self::fromString((string) $source->path);
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
