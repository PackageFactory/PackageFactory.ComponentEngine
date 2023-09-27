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

namespace PackageFactory\ComponentEngine\Language\Lexer\CharacterStream;

/**
 * @internal
 */
final class CharacterStream
{
    private int $byte;
    private ?string $characterUnderCursor = '';

    public function __construct(private readonly string $source)
    {
        $this->byte = 0;
        $this->next();
    }

    public function next(): void
    {
        if ($this->characterUnderCursor === null) {
            return;
        }

        $nextCharacter = $this->source[$this->byte++] ?? null;
        if ($nextCharacter === null) {
            $this->characterUnderCursor = null;
            return;
        }

        $ord  = ord($nextCharacter);
        if ($ord >= 0x80) {
            $nextCharacter .= $this->source[$this->byte++] ?? '';
        }
        if ($ord >= 0xe0) {
            $nextCharacter .= $this->source[$this->byte++] ?? '';
        }
        if ($ord >= 0xf0) {
            $nextCharacter .= $this->source[$this->byte++] ?? '';
        }

        $this->characterUnderCursor = $nextCharacter;
    }

    public function current(): ?string
    {
        return $this->characterUnderCursor;
    }

    public function isEnd(): bool
    {
        return $this->characterUnderCursor === null;
    }

    public function overwrite(CharacterStream $other): void
    {
        $other->byte = $this->byte;
        $other->characterUnderCursor = $this->characterUnderCursor;
    }

    public function getRemainder(): string
    {
        return $this->characterUnderCursor . substr($this->source, $this->byte);
    }
}
