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

namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class MatchArms implements \JsonSerializable
{
    /**
     * @var array<int,MatchArm>
     */
    private readonly array $arms;

    private function __construct(
        MatchArm ...$arms
    ) {
        $this->arms = $arms;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return self
     */
    public static function fromTokens(\Iterator $tokens): self
    {
        /** @var array<int,MatchArm> $arms */
        $arms = [];

        while (Scanner::type($tokens) !== TokenType::BRACKET_CURLY_CLOSE) {
            Scanner::skipSpaceAndComments($tokens);
            $arms[] = MatchArm::fromTokens($tokens);
        }

        return new self(...$arms);
    }

    public function jsonSerialize(): mixed
    {
        return $this->arms;
    }
}
