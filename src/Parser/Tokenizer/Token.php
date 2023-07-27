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

namespace PackageFactory\ComponentEngine\Parser\Tokenizer;

use PackageFactory\ComponentEngine\Parser\Source\Range;
use PackageFactory\ComponentEngine\Parser\Source\Fragment;
use PackageFactory\ComponentEngine\Parser\Source\Path;

final class Token
{
    private function __construct(
        public readonly TokenType $type,
        public readonly string $value,
        public readonly Range $boundaries,
        public readonly Path $sourcePath
    ) {
    }

    public static function fromFragment(
        TokenType $type,
        Fragment $fragment
    ): Token {
        return new Token(
            $type,
            $fragment->value,
            Range::from($fragment->start, $fragment->end),
            $fragment->source->path
        );
    }

    public static function emptyFromDelimitingFragments(
        TokenType $type,
        Fragment $startFragment,
        Fragment $endFragment
    ): Token {
        return new Token(
            $type,
            '',
            Range::from($startFragment->start, $endFragment->end),
            $startFragment->source->path
        );
    }

    public function equals(Token $other): bool
    {
        return ($this->type === $other->type
            && $this->value === $other->value
            && $this->boundaries->equals($other->boundaries)
            && $this->sourcePath->equals($other->sourcePath)
        );
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
