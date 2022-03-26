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
use PackageFactory\ComponentEngine\Parser\Ast\Child;
use PackageFactory\ComponentEngine\Parser\Ast\Key;
use PackageFactory\ComponentEngine\Parser\Ast\Literal;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\Ast\Value;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;

final class NumberLiteral implements Value, Literal, Term, Statement, Key, Child, \JsonSerializable
{
    public readonly float $number;

    public function __construct(public readonly Token $token)
    {
        $this->number = match (mb_substr($token->value, 0, 2)) {
            '0b', '0B' => bindec(mb_substr($token->value, 2)),
            '0o' => octdec(mb_substr($token->value, 2)),
            '0x' => hexdec(mb_substr($token->value, 2)),
            default => floatval($token->value),
        };
    }

    public static function fromTokenStream(TokenStream $stream): self
    {
        $value = $stream->current();
        if ($value->type === TokenType::NUMBER) {
            $stream->next();
            return new self($value);
        } else {
            throw ParserFailed::becauseOfUnexpectedToken(
                $stream->current(),
                [TokenType::NUMBER]
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
            'type' => 'NumberLiteral',
            'offset' => [
                $this->token->start->index,
                $this->token->end->index
            ],
            'value' => $this->token->value
        ];
    }
}
