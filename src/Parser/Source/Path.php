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

final class Path implements \JsonSerializable
{

    private function __construct(public readonly string $value)
    {
        if (empty(trim($value))) {
            throw new \Exception('@TODO: Invalid path');
        }
    }

    public static function createMemory(): self
    {
        return new self(':memory:');
    }

    public static function fromString(string $data): self
    {
        return new self($data);
    }

    public function isMemory(): bool
    {
        return $this->value === ':memory:';
    }

    public function isRelative(): bool
    {
        return $this->value[0] === '.';
    }

    public function isAbsolute(): bool
    {
        return $this->value[0] === '/';
    }

    public function getRelativePathTo(Path $target): self
    {
        if ($this->isMemory()) {
            throw new \Exception('@TODO: Cannot create relative path for :memory:');
        } elseif ($this->isRelative() && $target->isAbsolute()) {
            throw new \Exception('@TODO: Cannot create relative path from realtive source to asbolute target.');
        } elseif ($this->isAbsolute() && $target->isAbsolute()) {
            $dirname = dirname($this->value);
            if (substr($target->value, 0, strlen($dirname)) === $dirname) {
                return new self('.' . substr($target->value, strlen($dirname)));
            } else {
                throw new \Exception('@TODO: Cannot create relative path due to incompatible absolute paths.');
            }
        } else {
            $dirname = dirname($this->value);
            $resultSegments = explode('/', $dirname);
            $overflowSegments = [];
            $targetSegments = explode('/', $target->value);

            foreach ($targetSegments as $segment) {
                if ($segment === '.' || $segment === '') {
                    // ignore
                } elseif ($segment === '..') {
                    if (count($resultSegments)) {
                        array_pop($resultSegments);
                    } else {
                        $overflowSegments[] = $segment;
                    }
                } else {
                    $resultSegments[] = $segment;
                }
            }

            return new self(implode('/', [...$overflowSegments, ...$resultSegments]));
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): mixed
    {
        return $this->value;
    }
}
