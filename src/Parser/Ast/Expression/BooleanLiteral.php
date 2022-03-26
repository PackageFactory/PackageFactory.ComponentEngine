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

use PackageFactory\ComponentEngine\Exception\ParserFailed;
use PackageFactory\ComponentEngine\Parser\Ast\Literal;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\Ast\Value;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;

final class BooleanLiteral implements Value, Literal, Term, Statement, \JsonSerializable
{
    public readonly bool $boolean;

    private function __construct(public readonly Token $token)
    {
        $this->boolean = $token->value === 'true' ? true : false;
    }

    public static function fromTokenStream(TokenStream $stream): self
    {
        $value = $stream->current();
        if (($value->type === TokenType::KEYWORD_TRUE ||
            $value->type === TokenType::KEYWORD_FALSE
        )) {
            $stream->next();
            return new self(token: $value);
        } else {
            throw ParserFailed::becauseOfUnexpectedToken(
                $stream->current(),
                [
                    TokenType::KEYWORD_TRUE,
                    TokenType::KEYWORD_FALSE
                ]
            );
        }
    }

    public function __toString(): string
    {
        return $this->token->value;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => 'BooleanLiteral',
            'offset' => [
                $this->token->start->index,
                $this->token->end->index
            ],
            'value' => $this->token->value
        ];
    }
}
