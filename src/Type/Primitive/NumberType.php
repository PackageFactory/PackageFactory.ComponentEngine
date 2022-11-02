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

namespace PackageFactory\ComponentEngine\Type\Primitive;

use PackageFactory\ComponentEngine\Definition\BinaryOperator;
use PackageFactory\ComponentEngine\Type\Type;

final class NumberType extends Type
{
    private static null|self $instance = null;

    private function __construct()
    {
    }

    public static function create(): self
    {
        return self::$instance ??= new self();
    }

    public function binaryOperation(BinaryOperator $operator, Type $other): Type
    {
        return match ($operator) {
            BinaryOperator::PLUS,
            BinaryOperator::MINUS,
            BinaryOperator::MULTIPLY_BY,
            BinaryOperator::DIVIDE_BY,
            BinaryOperator::MODULO => match ($other::class) {
                NumberType::class => $this,
                default => parent::binaryOperation($operator, $other)
            },
            default => parent::binaryOperation($operator, $other)
        };
    }

    public function __toString(): string
    {
        return 'number';
    }
}
