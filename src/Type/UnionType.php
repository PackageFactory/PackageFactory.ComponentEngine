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

use PackageFactory\ComponentEngine\Parser\Ast\Reference\Identifier;

final class UnionType extends Type
{
    /**
     * @var Type[]
     */
    private readonly array $members;

    private function __construct(Type ...$members)
    {
        $flatMembers = [];
        foreach ($members as $member) {
            if ($member instanceof UnionType) {
                foreach ($member->members as $member) {
                    $flatMembers[] = $member;
                }
            } else {
                $flatMembers[] = $member;
            }
        }

        $this->members = array_unique($flatMembers);
    }

    public static function of(Type ...$members): self
    {
        return new self(...$members);
    }

    public function access(string $key): Type
    {
        $result = array_map(
            fn (Type $member) => $member->access($key),
            $this->members
        );

        $count = count($result);

        if ($count === 0) {
            parent::access($key);
        } elseif ($count === 1) {
            return $result[0];
        } else {
            return new self(...$result);
        }
    }

    public function __toString(): string
    {
        return sprintf(
            '(%s)',
            join(
                '|',
                array_map(
                    fn (Type $member) => (string) $member,
                    $this->members
                )
            )
        );
    }
}
