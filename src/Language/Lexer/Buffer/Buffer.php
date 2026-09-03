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

namespace PackageFactory\ComponentEngine\Language\Lexer\Buffer;

use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Range;

final class Buffer
{
    private Position $start;
    private int $endLineNumber;
    private int $nextEndLineNumber;
    private int $endColumnNumber;
    private int $nextEndColumnNumber;
    private string $contents;

    public function __construct()
    {
        $this->start = Position::zero();
        $this->endLineNumber = 0;
        $this->nextEndLineNumber = 0;
        $this->endColumnNumber = 0;
        $this->nextEndColumnNumber = 0;
        $this->contents = '';
    }

    public function getStart(): Position
    {
        return $this->start;
    }

    public function getEnd(): Position
    {
        return Position::from($this->endLineNumber, $this->endColumnNumber);
    }

    public function getRange(): Range
    {
        return Range::from($this->getStart(), $this->getEnd());
    }

    public function getContents(): string
    {
        return $this->contents;
    }

    public function append(?string $character): void
    {
        if ($character === null) {
            return;
        }

        $this->contents .= $character;

        $this->endLineNumber = $this->nextEndLineNumber;
        $this->endColumnNumber = $this->nextEndColumnNumber;

        if ($character === "\n") {
            $this->nextEndLineNumber++;
            $this->nextEndColumnNumber = 0;
        } else {
            $this->nextEndColumnNumber++;
        }
    }

    public function flush(): void
    {
        $this->start = Position::from(
            $this->endLineNumber = $this->nextEndLineNumber,
            $this->endColumnNumber = $this->nextEndColumnNumber
        );

        $this->contents = '';
    }

    public function overwrite(Buffer $other): void
    {
        $other->start = $this->start;
        $other->endLineNumber = $this->endLineNumber;
        $other->nextEndLineNumber = $this->nextEndLineNumber;
        $other->endColumnNumber = $this->endColumnNumber;
        $other->nextEndColumnNumber = $this->nextEndColumnNumber;
        $other->contents = $this->contents;
    }

    public function reset(): void
    {
        $this->endLineNumber = $this->nextEndLineNumber = $this->start->lineNumber;
        $this->endColumnNumber = $this->nextEndColumnNumber = $this->start->columnNumber;
        $this->contents = '';
    }
}
