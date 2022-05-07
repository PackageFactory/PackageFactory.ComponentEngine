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

final class Operands implements \JsonSerializable
{
    /**
     * @var array<int,Expression>
     */
    private readonly array $operands;

    private function __construct(
        Expression ...$operands
    ) {
        $this->operands = $operands;
    }

    /**
     * @param Expression $first
     * @param \Iterator<mixed,Token> $tokens
     * @param BinaryOperator $operator
     * @return self
     */
    public static function fromTokens(Expression $first, \Iterator $tokens, BinaryOperator $operator): self
    {
        $precedence = $operator->toPrecedence();
        $operands = [$first];

        while (true) {
            Scanner::skipSpaceAndComments($tokens);

            $operands[] = Expression::fromTokens($tokens, $precedence);

            Scanner::skipSpaceAndComments($tokens);

            switch (Scanner::type($tokens)) {
                case TokenType::BRACKET_ROUND_CLOSE:
                case TokenType::BRACKET_CURLY_CLOSE:
                case TokenType::BRACKET_SQUARE_CLOSE:
                case TokenType::ARROW_SINGLE:
                case TokenType::QUESTIONMARK:
                    break 2;
                case $operator->toTokenType():
                    Scanner::skipOne($tokens);
                    break;
                default:
                    if ($precedence->mustStopAt(Scanner::type($tokens))) {
                        break 2;
                    } else {
                        Scanner::assertType($tokens, $operator->toTokenType());
                    }
                    break;
            }
        }

        return new self(...$operands);
    }

    public function jsonSerialize(): mixed
    {
        return $this->operands;
    }
}
