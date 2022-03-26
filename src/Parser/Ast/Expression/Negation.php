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
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;

final class Negation implements Term, Statement, \JsonSerializable
{
    private function __construct(
        public readonly Token $token,
        public readonly Term $subject
    ) {
    }

    public static function fromTokenStream(TokenStream $stream): self
    {
        $stream->skipWhiteSpaceAndComments();

        $value = $stream->current();
        if ($value->type === TokenType::OPERATOR_LOGICAL_NOT) {
            $stream->next();
            return new self(
                token: $value,
                subject: ExpressionParser::parseTerm($stream)
            );
        } else {
            throw ParserFailed::becauseOfUnexpectedToken(
                $stream->current(),
                [TokenType::OPERATOR_LOGICAL_NOT]
            );
        }
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => 'Negation',
            'offset' => [
                $this->token->start->index,
                $this->token->end->index
            ],
            'subject' => $this->subject
        ];
    }
}
