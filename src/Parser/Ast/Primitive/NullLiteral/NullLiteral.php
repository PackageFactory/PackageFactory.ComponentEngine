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

namespace PackageFactory\ComponentEngine\Parser\Ast\Primitive\NullLiteral;

use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenStream\TokenStream;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class NullLiteral implements \JsonSerializable
{
    private function __construct(public readonly Token $token)
    {
        if ($token->type !== TokenType::KEYWORD) {
            throw NullLiteralInvariantViolation::becauseOfUnexpectedToken(
                $token
            );
        }

        if ($token->value !== "null") {
            throw NullLiteralInvariantViolation::becauseOfUnexpectedKeyWord(
                $token->value
            );
        }
    }

    public static function peekInto(TokenStream $tokenStream): bool
    {
        return $tokenStream->current()->type === TokenType::KEYWORD;
    }

    public static function fromToken(Token $token): self
    {
        return new self(token: $token);
    }

    public function __toString(): string
    {
        return $this->token->value;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => 'NullLiteral',
            'boundaries' => $this->token->boundaries
        ];
    }
}
