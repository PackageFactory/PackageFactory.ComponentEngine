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

namespace PackageFactory\ComponentEngine\Parser\Lexer;

use PackageFactory\ComponentEngine\Parser\Source\Fragment;

final class Capture
{
    private function __construct(private ?Fragment $fragment = null)
    {
    }

    public static function createEmpty(): self
    {
        return new self(null);
    }

    public static function fromFragment(Fragment $fragment): self
    {
        return new self($fragment);
    }

    public function append(Fragment $add): void
    {
        if ($this->fragment === null) {
            $this->fragment = $add;
        } else {
            $this->fragment = $this->fragment->append($add);
        }
    }

    /**
     * @param TokenType $tokenType
     * @return \Iterator<Token>
     */
    public function flush(TokenType $tokenType): \Iterator
    {
        if ($this->fragment !== null) {
            yield Token::fromFragment($tokenType, $this->fragment);
            $this->fragment = null;
        }
    }
}
