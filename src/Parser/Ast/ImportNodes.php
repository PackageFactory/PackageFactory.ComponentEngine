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

namespace PackageFactory\ComponentEngine\Parser\Ast;

final class ImportNodes implements \JsonSerializable
{
    /**
     * @var array<string,ImportNode>
     */
    public readonly array $items;

    /**
     * @param array<string,ImportNode> $items
     */
    private function __construct(
        array $items
    ) {
        $this->items = $items;
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function withAddedImport(ImportNode $import): self
    {
        $name = $import->name->value;

        if (array_key_exists($name, $this->items)) {
            throw new \Exception('@TODO: Duplicate Import ' . $name);
        }

        return new self([...$this->items, ...[$name => $import]]);
    }

    public function get(string $name): ?ImportNode
    {
        return $this->items[$name] ?? null;
    }

    public function jsonSerialize(): mixed
    {
        return array_values($this->items);
    }
}
