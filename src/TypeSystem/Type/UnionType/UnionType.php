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

namespace PackageFactory\ComponentEngine\TypeSystem\Type\UnionType;

use PackageFactory\ComponentEngine\TypeSystem\AtomicTypeInterface;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

final class UnionType implements TypeInterface
{
    /**
     * @var AtomicTypeInterface[]
     */
    public readonly array $members;

    private function __construct(AtomicTypeInterface ...$members)
    {
        $this->members = $members;
    }

    public static function of(AtomicTypeInterface ...$members): TypeInterface
    {
        $uniqueMembers = [];
        foreach ($members as $member) {
            foreach ($uniqueMembers as $uniqueMember) {
                if ($member->is($uniqueMember)) {
                    continue 2;
                }
            }

            $uniqueMembers[] = $member;
        }

        if (count($uniqueMembers) === 1) {
            return $uniqueMembers[0];
        }

        return new self(...$members);
    }

    public static function merge(TypeInterface ...$items): TypeInterface
    {
        $members = [];

        foreach ($items as $item) {
            if ($item instanceof UnionType) {
                $members = array_merge($members, $item->members);
            } else if ($item instanceof AtomicTypeInterface) {
                $members[] = $item;
            } else {
                throw new \Exception('@TODO: Unable to merge type ' . $item::class);
            }
        }

        return self::of(...$members);
    }

    public function is(TypeInterface $other): bool
    {
        if ($other instanceof UnionType) {
            foreach ($this->members as $member) {
                $match = false;
                foreach ($other->members as $otherMember) {
                    if ($otherMember->is($member)) {
                        $match = true;
                        break;
                    }
                }

                if (!$match) {
                    return false;
                }
            }

            return true;
        } else {
            return false;
        }
    }
}
