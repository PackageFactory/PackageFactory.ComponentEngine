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

use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

final class UnionType implements TypeInterface
{
    /**
     * @var TypeInterface[]
     */
    private array $members;

    private function __construct(TypeInterface ...$members)
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
        $this->members = $uniqueMembers;
    }

    public static function of(TypeInterface ...$members): TypeInterface
    {
        $union = new self(...$members);

        if (count($union->members) === 1) {
            return $union->members[0];
        }

        return new self(...$members);
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
