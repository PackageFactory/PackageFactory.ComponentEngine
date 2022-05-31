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

use PackageFactory\ComponentEngine\Type\Primitive\BooleanType;
use PackageFactory\ComponentEngine\Type\Primitive\NumberType;
use PackageFactory\ComponentEngine\Type\Primitive\StringType;

final class LiteralType extends Type
{
    private function __construct(
        public readonly int|float|bool|string $value
    ) {
    }

    public static function of(int|float|bool|string $value): self
    {
        return new self($value);
    }

    public function toPrimitiveType(): NumberType|BooleanType|StringType
    {
        if (is_int($this->value) || is_float($this->value)) {
            return NumberType::create();
        } elseif (is_bool($this->value)) {
            return BooleanType::create();
        } else {
            return StringType::create();
        }
    }

    public function __toString(): string
    {
        if (is_string($this->value)) {
            return sprintf('"%s"', addcslashes($this->value, '\n\r"'));
        }

        return (string) $this->value;
    }
}
