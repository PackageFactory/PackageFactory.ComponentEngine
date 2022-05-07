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

final class Expressions implements \JsonSerializable
{
    /**
     * @var array<int,Expression>
     */
    private readonly array $expressions;

    private function __construct(
        Expression ...$expressions
    ) {
        $this->expressions = $expressions;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return self
     */
    public static function fromTokens(\Iterator $tokens): self
    {
        $expressions = [];
        while (true) {
            Scanner::skipSpaceAndComments($tokens);

            switch (Scanner::type($tokens)) {
                case TokenType::BRACKET_ROUND_CLOSE:
                    break 2;
                default:
                    $expressions[] = Expression::fromTokens($tokens);
                    break;
            }

            Scanner::skipSpace($tokens);
            if (Scanner::type($tokens) === TokenType::BRACKET_ROUND_CLOSE) {
                break;
            }
            if (Scanner::type($tokens) === TokenType::ARROW_SINGLE) {
                break;
            }

            Scanner::assertType($tokens, TokenType::COMMA);
            Scanner::skipOne($tokens);
        }
        
        return new self(...$expressions);
    }

    public function jsonSerialize(): mixed
    {
        return $this->expressions;
    }
}
