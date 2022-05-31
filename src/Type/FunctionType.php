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

final class FunctionType extends Type
{
    private function __construct(
        public readonly Tuple $parameterTypes,
        public readonly Type $returnType
    ) {
    }

    public static function create(Tuple $parameterTypes, Type $returnType): self
    {
        return new self($parameterTypes, $returnType);
    }

    public function getReturnType(): Type
    {
        return $this->returnType;
    }

    public function getFunctionType(): FunctionType
    {
        return $this;
    }

    public function __toString(): string
    {
        return sprintf(
            '%s=>%s',
            (string) $this->parameterTypes,
            (string) $this->returnType
        );
    }
}
