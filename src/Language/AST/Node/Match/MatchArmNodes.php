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

namespace PackageFactory\ComponentEngine\Language\AST\Node\Match;

final class MatchArmNodes
{
    /**
     * @var MatchArmNode[]
     */
    public readonly array $items;

    private ?MatchArmNode $defaultArm = null;

    public function __construct(MatchArmNode ...$items)
    {
        if (count($items) === 0) {
            throw InvalidMatchArmNodes::becauseTheyWereEmpty();
        }

        foreach ($items as $item) {
            if ($item->isDefault()) {
                if (is_null($this->defaultArm)) {
                    $this->defaultArm = $item;
                } else {
                    throw InvalidMatchArmNodes::becauseTheyContainMoreThanOneDefaultMatchArmNode(
                        secondDefaultMatchArmNode: $item
                    );
                }
            }
        }

        $this->items = $items;
    }
}
