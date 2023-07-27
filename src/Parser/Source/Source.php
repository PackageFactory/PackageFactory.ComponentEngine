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

namespace PackageFactory\ComponentEngine\Parser\Source;

/**
 * @implements \IteratorAggregate<mixed, Fragment>
 */
final class Source implements \IteratorAggregate
{
    public function __construct(
        public readonly Path $path,
        public readonly string $contents
    ) {
    }

    public static function fromString(string $contents): Source
    {
        return new Source(Path::createMemory(), $contents);
    }

    public static function fromFile(string $filename): Source
    {
        if ($contents = file_get_contents($filename)) {
            return new Source(Path::fromString($filename), $contents);
        }

        throw new \Exception('@TODO: Could not load file');
    }

    public function equals(Source $other): bool
    {
        return $this->contents === $other->contents;
    }

    /**
     * @return \Iterator<Fragment>
     */
    public function getIterator(): \Iterator
    {
        $rowIndex = 0;
        $columnIndex = 0;
        $length = strlen($this->contents);

        for ($index = 0; $index < $length; $index++) {
            $character = $this->contents[$index];

            yield Fragment::create(
                $character,
                Position::from($index, $rowIndex, $columnIndex),
                Position::from($index, $rowIndex, $columnIndex),
                $this
            );

            if ($character === "\n") {
                $rowIndex++;
                $columnIndex = 0;
            } else {
                $columnIndex++;
            }
        }
    }
}
