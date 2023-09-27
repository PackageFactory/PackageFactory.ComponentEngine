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

namespace PackageFactory\ComponentEngine\Language\Lexer\Matcher\Sequence;

use PackageFactory\ComponentEngine\Language\Lexer\Matcher\Result;
use PackageFactory\ComponentEngine\Language\Lexer\Matcher\MatcherInterface;

final class Sequence implements MatcherInterface
{
    private int $lastStop = 0;
    private int $matcherIndex = 0;
    private int $numberOfMatchers;

    /**
     * @var MatcherInterface[]
     */
    private readonly array $matchers;

    public function __construct(MatcherInterface ...$matchers)
    {
        $this->matchers = $matchers;
        $this->numberOfMatchers = count($matchers);
        assert($this->numberOfMatchers > 0);
    }

    public function match(?string $character, int $offset): Result
    {
        if ($offset === 0) {
            $this->lastStop = 0;
            $this->matcherIndex = 0;
        }

        $matcher = $this->matchers[$this->matcherIndex] ?? null;
        assert($matcher !== null);

        $result = $matcher->match($character, $offset - $this->lastStop);
        if ($result === Result::SATISFIED) {
            $this->matcherIndex++;
            if ($this->matcherIndex === $this->numberOfMatchers) {
                return $result;
            }

            $this->lastStop = $offset;
            $matcher = $this->matchers[$this->matcherIndex] ?? null;
            assert($matcher !== null);

            return $matcher->match($character, 0);
        }

        return $result;
    }
}
