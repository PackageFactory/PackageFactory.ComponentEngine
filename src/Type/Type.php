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

use PackageFactory\ComponentEngine\Definition\BinaryOperator;
use PackageFactory\ComponentEngine\Type\Primitive\BooleanType;

abstract class Type implements \JsonSerializable
{
    public function expand(Type $other): Type
    {
        return $other === $this ? $this : UnionType::of($this, $other);
    }

    public function binaryOperation(BinaryOperator $operator, Type $other): Type
    {
        return match ($operator) {
            BinaryOperator::EQUAL,
            BinaryOperator::LESS_THAN,
            BinaryOperator::LESS_THAN_OR_EQUAL,
            BinaryOperator::GREATER_THAN,
            BinaryOperator::GREATER_THAN_OR_EQUAL => BooleanType::create(),
            default => throw new \Exception('@TODO: Illegal binary operation: ' . $this . ' ' . $operator->value . ' ' . $other)
        };
    }

    public function access(string $key): Type
    {
        throw new \Exception('@TODO: Illegal access to property ' . $key . ' on type ' . $this);
    }

    abstract public function __toString(): string;

    final public function jsonSerialize(): mixed
    {
        return (string) $this;
    }
}
