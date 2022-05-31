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

namespace PackageFactory\ComponentEngine\Type\Record;

use PackageFactory\ComponentEngine\Type\Tuple;
use PackageFactory\ComponentEngine\Type\Type;

final class RecordType extends Type
{
    /**
     * @var RecordEntry[]
     */
    private readonly array $entries;

    private function __construct(
        RecordEntry ...$entries
    ) {
        $this->entries = $entries;
    }

    public static function of(RecordEntry ...$entries): self
    {
        return new self(...$entries);
    }

    public function get(string $name): ?RecordEntry
    {
        foreach ($this->entries as $entry) {
            if ($entry->name === $name) {
                return $entry;
            }
        }

        return null;
    }

    public function access(string $key): Type
    {
        return $this->get($key)?->type ?? throw new \Exception('@TODO: Unknown Property ' . $key);
    }

    public function toTuple(): Tuple
    {
        /** @var Type[] $members */
        $members = array_map(
            fn ($e) => $e->type,
            $this->entries
        );

        return Tuple::of(...$members);
    }

    public function __toString(): string
    {
        return sprintf(
            '{%s}',
            join(
                ';',
                array_map(
                    fn (RecordEntry $entry) => sprintf(
                        '%s:%s',
                        $entry->name,
                        (string) $entry->type
                    ),
                    $this->entries
                )
            )
        );
    }
}
