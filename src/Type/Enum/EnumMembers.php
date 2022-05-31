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

namespace PackageFactory\ComponentEngine\Type\Enum;

use PackageFactory\ComponentEngine\Parser\Ast\Reference\Identifier;

final class EnumMembers implements \JsonSerializable
{
    /**
     * @var EnumMember[]
     */
    private readonly array $items;

    private function __construct(
        EnumMember ...$items
    ) {
        $this->items = $items;
    }

    public static function of(EnumMember ...$items): self
    {
        return new self(...$items);
    }

    public function get(string $name): ?EnumMember
    {
        foreach ($this->items as $item) {
            if ($item->name === $name) {
                return $item;
            }
        }

        return null;
    }

    public function jsonSerialize(): mixed
    {
        return array_reduce(
            $this->items,
            function (array $acc, EnumMember $member) {
                $acc[$member->name] = $member;
                return $acc;
            },
            []
        );
    }
}
