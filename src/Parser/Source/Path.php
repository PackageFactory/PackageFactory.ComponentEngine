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
    public readonly string $value;
    private readonly ?string $driveLetter;
    private readonly string $pathWithoutDriveLetter;

    private function __construct(string $value)
    {
        $this->value = ($value[0] === DIRECTORY_SEPARATOR ? DIRECTORY_SEPARATOR : '') . join(
            DIRECTORY_SEPARATOR,
            array_filter(
                explode(
                    DIRECTORY_SEPARATOR,
                    mb_ereg_replace('\\\\|/', DIRECTORY_SEPARATOR, $value, 'msr') ?: ''
                ),
                'mb_strlen'
            )
        );

        if (empty(trim($this->value))) {
            throw new \Exception('@TODO: Invalid path');
        }

        preg_match('/^[a-zA-Z]:/', $this->value, $matches);
        $this->driveLetter = isset($matches[0]) ? $matches[0] : null;
        $this->pathWithoutDriveLetter = $this->driveLetter
            ? mb_substr($this->value, mb_strlen($this->driveLetter))
            : $this->value;
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
        return $this->pathWithoutDriveLetter[0] === DIRECTORY_SEPARATOR;
    }

    public function resolveRelationTo(Path $other): self
    {
        if ($this->isAbsolute() && $other->isRelative()) {
            $pathSegments = array_merge(
                explode(DIRECTORY_SEPARATOR, dirname($this->pathWithoutDriveLetter)),
                explode(DIRECTORY_SEPARATOR, $other->value)
            );

            $absolutePathSegments = [];
            foreach ($pathSegments as $pathSegment) {
                switch ($pathSegment) {
                    case '.':
                        continue 2;
                    case '..':
                        if ($absolutePathSegments) {
                            array_pop($absolutePathSegments);
                        } else {
                            throw new \Exception('@TODO: Unable to resolve path ' . $other->value);
                        }
                        break;
                    default:
                        $absolutePathSegments[] = $pathSegment;
                        break;
                }
            }

            return new self(
                ($this->driveLetter ? $this->driveLetter : '')
                . DIRECTORY_SEPARATOR
                . join(DIRECTORY_SEPARATOR, $absolutePathSegments)
            );
        } else {
            return $other;
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
