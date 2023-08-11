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

use PackageFactory\ComponentEngine\Parser\Source\Position;

/**
 * @internal
 */
final class Cursor
{
    private int $currentLineNumber = 0;
    private int $currentColumnNumber = 0;
    private int $previousLineNumber = -1;
    private int $previousColumnNumber = -1;

    public function advance(?string $character): void
    {
        if ($character !== null) {
            $this->previousLineNumber = $this->currentLineNumber;
            $this->previousColumnNumber = $this->currentColumnNumber;

            if ($character === "\n") {
                $this->currentLineNumber++;
                $this->currentColumnNumber = 0;
            } else {
                $this->currentColumnNumber++;
            }
        }
    }

    public function getCurrentPosition(): Position
    {
        return new Position($this->currentLineNumber, $this->currentColumnNumber);
    }

    public function getPreviousPosition(): Position
    {
        assert($this->previousLineNumber >= 0);
        assert($this->previousColumnNumber >= 0);

        return new Position($this->previousLineNumber, $this->previousColumnNumber);
    }

    public function makeSnapshot(): CursorSnapshot
    {
        return new CursorSnapshot(
            currentLineNumber: $this->currentLineNumber,
            currentColumnNumber: $this->currentColumnNumber,
            previousLineNumber: $this->previousLineNumber,
            previousColumnNumber: $this->previousColumnNumber
        );
    }

    public function restoreSnapshot(CursorSnapshot $snapshot): void
    {
        $this->currentLineNumber = $snapshot->currentLineNumber;
        $this->currentColumnNumber = $snapshot->currentColumnNumber;
        $this->previousLineNumber = $snapshot->previousLineNumber;
        $this->previousColumnNumber = $snapshot->previousColumnNumber;
    }
}
