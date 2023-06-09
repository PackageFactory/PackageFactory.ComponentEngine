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

use PackageFactory\ComponentEngine\TypeSystem\Type\NullType\NullType;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

/**
 * @implements \IteratorAggregate<int, TypeInterface>
 */
final class UnionType implements TypeInterface, \IteratorAggregate, \Countable
{
    /**
     * @var TypeInterface[]
     */
    private readonly array $members;

    private function __construct(TypeInterface ...$members)
    {
        assert(count($members) > 1, 'UnionType must hold at least two different members');
        $this->members = $members;
    }

    public static function of(TypeInterface $firstMember, TypeInterface ...$members): TypeInterface
    {
        $uniqueMembers = [];
        foreach ([$firstMember, ...$members] as $member) {
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

        return new self(...$uniqueMembers);
    }

    public function containsNull(): bool
    {
        foreach ($this->members as $member) {
            if ($member->is(NullType::get())) {
                return true;
            }
        }
        return false;
    }

    public function withoutNull(): TypeInterface
    {
        $nonNullMembers = [];
        foreach ($this->members as $member) {
            if ($member->is(NullType::get())) {
                continue;
            }
            $nonNullMembers[] = $member;
        }
        return self::of(...$nonNullMembers);
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

    /** @return \Iterator<int, TypeInterface> */
    public function getIterator(): \Iterator
    {
        yield from $this->members;
    }

    /** @return array<int, TypeInterface> */
    public function toArray(): array
    {
        return $this->members;
    }

    public function count(): int
    {
        return count($this->members);
    }
}
