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
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;

final class StringLiteral implements Value, Literal, Term, Statement, Key, Child, \JsonSerializable
{
    private function __construct(
        public readonly Token $start,
        public readonly Token $end,
        public readonly string $value
    ) {
    }

    public static function fromTokenStream(TokenStream $stream): self
    {
        $start = $stream->current();
        $stream->consume(TokenType::STRING_LITERAL_START);

        $value = '';
        while ($stream->valid()) {
            switch ($stream->current()->type) {
                case TokenType::STRING_LITERAL_CONTENT:
                    $value .= $stream->current()->value;
                    $stream->next();
                    break;

                case TokenType::STRING_LITERAL_ESCAPE:
                    $stream->next();
                    break;

                case TokenType::STRING_LITERAL_ESCAPED_CHARACTER:
                    $value .= $stream->current()->value;
                    $stream->next();
                    break;

                case TokenType::STRING_LITERAL_END:
                    break 2;

                default:
                    throw ParserFailed::becauseOfUnexpectedToken(
                        $stream->current(),
                        [
                            TokenType::STRING_LITERAL_CONTENT,
                            TokenType::STRING_LITERAL_ESCAPE,
                            TokenType::STRING_LITERAL_ESCAPED_CHARACTER,
                            TokenType::STRING_LITERAL_END
                        ]
                    );
            }
        }

        $end = $stream->current();
        $stream->consume(TokenType::STRING_LITERAL_END);

        return new self(
            start: $start,
            end: $end,
            value: $value
        );
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => 'StringLiteral',
            'offset' => [
                $this->start->start->index,
                $this->end->end->index
            ],
            'value' => $this->value
        ];
    }
}
