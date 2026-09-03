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

namespace PackageFactory\ComponentEngine\Language\Lexer\Matcher\Not;

use PackageFactory\ComponentEngine\Language\Lexer\Matcher\Result;
use PackageFactory\ComponentEngine\Language\Lexer\Matcher\MatcherInterface;

final class Not implements MatcherInterface
{
    public function __construct(private readonly MatcherInterface $innerMatcher)
    {
    }

    public function match(?string $character, int $offset): Result
    {
        $inner = $this->innerMatcher->match($character, 0);

        return match (true) {
            $character === null =>
                Result::SATISFIED,
            $offset === 0 => match ($inner) {
                Result::KEEP => Result::CANCEL,
                default => Result::KEEP
            },
            default => match ($inner) {
                Result::KEEP => Result::SATISFIED,
                default => Result::KEEP
            }
        };
    }
}
