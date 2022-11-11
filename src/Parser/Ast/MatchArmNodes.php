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

namespace PackageFactory\ComponentEngine\Parser\Ast;

use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class MatchArmNodes implements \JsonSerializable
{
    /**
     * @var MatchArmNode[]
     */
    public readonly array $items;

    private function __construct(
        MatchArmNode ...$items
    ) {
        $this->items = $items;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return self
     */
    public static function fromTokens(\Iterator $tokens): self
    {
        $items = [];

        while (Scanner::type($tokens) !== TokenType::BRACKET_CURLY_CLOSE) {
            Scanner::skipSpaceAndComments($tokens);
            $items[] = MatchArmNode::fromTokens($tokens);
        }

        return new self(...$items);
    }

    public function jsonSerialize(): mixed
    {
        return $this->items;
    }
}
