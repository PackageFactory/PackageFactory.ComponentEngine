<?php

/**
 * PackageFactory.ComponentEngine - Universal View Components for PHP
 *   Copyright (C) 2023 Contributors of PackageFactory.ComponentEngine
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

namespace PackageFactory\ComponentEngine\Language\AST\Node\TypeReference;

use PackageFactory\ComponentEngine\Domain\TypeName\TypeNames;

final class TypeNameNodes
{
    /**
     * @var TypeNameNode[]
     */
    public readonly array $items;

    private ?TypeNames $cachedTypeNames = null;

    public function __construct(TypeNameNode ...$items)
    {
        if (count($items) === 0) {
            throw InvalidTypeNameNodes::becauseTheyWereEmpty();
        }

        $typeNames = [];
        foreach ($items as $item) {
            if (isset($typeNames[$item->value->value])) {
                throw InvalidTypeNameNodes::becauseTheyContainDuplicates(
                    duplicateTypeNameNode: $item
                );
            }

            $typeNames[$item->value->value] = true;
        }

        $this->items = $items;
    }

    public function getSize(): int
    {
        return count($this->items);
    }

    public function toTypeNames(): TypeNames
    {
        if ($this->cachedTypeNames === null) {
            $typeNamesAsArray = array_map(
                static fn (TypeNameNode $typeNameNode) => $typeNameNode->value,
                $this->items
            );

            $this->cachedTypeNames = new TypeNames(...$typeNamesAsArray);
        }

        return $this->cachedTypeNames;
    }
}
