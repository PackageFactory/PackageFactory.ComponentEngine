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

namespace PackageFactory\ComponentEngine\Target\Php\TargetSpecific;

final class ClassName
{
    /**
     * @var array<string,ClassName>
     */
    private static array $instances;

    /**
     * @var string[]
     */
    private array $segments;

    private function __construct(private readonly string $fullyQualifiedClassName)
    {
        $this->segments = explode('\\', $this->fullyQualifiedClassName);
    }

    public static function fromString(string $string): self
    {
        return self::$instances[$string] ??= new self($string);
    }

    public function getFullyQualifiedClassName(): string
    {
        return $this->fullyQualifiedClassName;
    }

    public function getNamespace(): string
    {
        return join('\\', array_slice($this->segments, 0, -1));
    }

    public function getShortClassName(): string
    {
        return $this->segments[count($this->segments) - 1];
    }
}
