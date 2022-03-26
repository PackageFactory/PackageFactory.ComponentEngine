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

namespace PackageFactory\ComponentEngine\Parser\Source;

final class Position implements \JsonSerializable
{
    private function __construct(
        public readonly int $index,
        public readonly int $rowIndex,
        public readonly int $columnIndex
    ) {
    }

    public static function create(
        int $index,
        int $rowIndex,
        int $columnIndex
    ): Position {
        return new Position(
            index: $index,
            rowIndex: $rowIndex,
            columnIndex: $columnIndex
        );
    }

    public function equals(Position $other): bool
    {
        return $this->index === $other->index;
    }

    public function gt(Position $other): bool
    {
        return $this->index > $other->index;
    }

    public function gte(Position $other): bool
    {
        return $this->gt($other) || $this->equals($other);
    }

    public function lt(Position $other): bool
    {
        return $this->index < $other->index;
    }

    public function lte(Position $other): bool
    {
        return $this->lt($other) || $this->equals($other);
    }

    public function jsonSerialize(): mixed
    {
        return $this->index;
    }
}
