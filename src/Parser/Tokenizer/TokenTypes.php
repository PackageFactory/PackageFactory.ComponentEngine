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

namespace PackageFactory\ComponentEngine\Parser\Tokenizer;

final class TokenTypes
{
    /**
     * @var TokenType[]
     */
    private readonly array $items;

    private function __construct(TokenType ...$items)
    {
        assert(count($items) > 0);

        $this->items = $items;
    }

    public static function from(TokenType ...$items): self
    {
        $items = array_unique($items, SORT_REGULAR);
        $items = array_values($items);

        return new self(...$items);
    }

    public function contains(TokenType $needle): bool
    {
        return in_array($needle, $this->items);
    }

    public function toDebugString(): string
    {
        if (count($this->items) === 1) {
            return $this->items[0]->toDebugString();
        }

        $leadingItems = array_slice($this->items, 0, -1);
        $trailingItem = array_slice($this->items, -1)[0];

        return join(', ', array_map(
            static fn (TokenType $tokenType) => $tokenType->toDebugString(),
            $leadingItems
        )) . ' or ' . $trailingItem->toDebugString();
    }
}
