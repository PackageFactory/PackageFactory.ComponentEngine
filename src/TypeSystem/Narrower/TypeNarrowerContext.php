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

use PackageFactory\ComponentEngine\Definition\BinaryOperator;
use PackageFactory\ComponentEngine\TypeSystem\Type\NullType\NullType;
use PackageFactory\ComponentEngine\TypeSystem\Type\UnionType\UnionType;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

enum TypeNarrowerContext
{
    case TRUTHY;

    case FALSY;

    public function negate(): self
    {
        return match ($this) {
            self::TRUTHY => self::FALSY,
            self::FALSY => self::TRUTHY
        };
    }

    public function basedOnBinaryOperator(BinaryOperator $operator): ?self
    {
        return match ($operator) {
            BinaryOperator::EQUAL => $this,
            BinaryOperator::NOT_EQUAL => $this->negate(),
            default => null,
        };
    }

    public function narrowType(TypeInterface $type): TypeInterface
    {
        if (!$type instanceof UnionType || !$type->containsNull()) {
            return $type;
        }
        return match ($this) {
            self::TRUTHY => $type->withoutNull(),
            self::FALSY => NullType::get()
        };
    }
}
