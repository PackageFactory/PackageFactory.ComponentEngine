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

final class Position
{
    private static ?self $zero;

    public function __construct(
        public readonly int $lineNumber,
        public readonly int $columnNumber
    ) {
    }

    public static function zero(): self
    {
        return self::$zero ??= new self(0, 0);
    }

    public static function from(int $lineNumber, int $columnNumber): self
    {
        return new self($lineNumber, $columnNumber);
    }

    public function toDebugString(): string
    {
        return sprintf('line %s, column %s', $this->lineNumber, $this->columnNumber);
    }

    public function toRange(?Position $endPosition = null): Range
    {
        return Range::from($this, $endPosition ?? $this);
    }
}
