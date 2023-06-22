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

final class ExpressionNodes implements \JsonSerializable
{
    /**
     * @var ExpressionNode[]
     */
    public readonly array $items;

    public function __construct(
        ExpressionNode ...$items
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
        Scanner::skipSpaceAndComments($tokens);

        switch (Scanner::type($tokens)) {
            case TokenType::BRACKET_ROUND_CLOSE:
            case TokenType::BRACKET_CURLY_CLOSE:
            case TokenType::BRACKET_SQUARE_CLOSE:
            case TokenType::ARROW_SINGLE:
                return new self();
            default:
                break;
        }

        while (true) {
            $items[] = ExpressionNode::fromTokens($tokens);

            Scanner::skipSpaceAndComments($tokens);

            switch (Scanner::type($tokens)) {
                case TokenType::BRACKET_ROUND_CLOSE:
                case TokenType::BRACKET_CURLY_CLOSE:
                case TokenType::BRACKET_SQUARE_CLOSE:
                case TokenType::ARROW_SINGLE:
                    break 2;
                default:
                    Scanner::assertType($tokens, TokenType::COMMA);
                    Scanner::skipOne($tokens);
                    break;
            }

            Scanner::skipSpaceAndComments($tokens);
        }

        return new self(...$items);
    }

    public function jsonSerialize(): mixed
    {
        return $this->items;
    }
}
