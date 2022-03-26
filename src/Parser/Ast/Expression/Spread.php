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
use PackageFactory\ComponentEngine\Parser\Ast\ParameterAssignment;
use PackageFactory\ComponentEngine\Parser\Ast\Spreadable;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;

final class Spread implements Statement, ParameterAssignment, \JsonSerializable
{
    private function __construct(
        public readonly Token $token,
        public readonly Spreadable $subject
    ) {
    }

    public static function fromTokenStream(TokenStream $stream): self
    {
        $value = $stream->current();
        if ($value->type === TokenType::OPERATOR_SPREAD) {
            $stream->next();

            $token = $stream->current();
            $subject = ExpressionParser::parseTerm($stream, ExpressionParser::PRIORITY_LIST);
            if ($subject instanceof Spreadable) {
                return new self(
                    token: $value,
                    subject: $subject
                );
            } else {
                throw ParserFailed::becauseOfUnexpectedTerm(
                    $token,
                    $subject,
                    [
                        ArrayLiteral::class,
                        ObjectLiteral::class,
                        Chain::class,
                        Conjunction::class,
                        Disjunction::class,
                        Identifier::class,
                        Ternary::class
                    ]
                );
            }
        } else {
            throw ParserFailed::becauseOfUnexpectedToken(
                $stream->current(),
                [TokenType::OPERATOR_SPREAD]
            );
        }
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => 'Spread',
            'offset' => [
                $this->token->start->index,
                $this->token->end->index
            ],
            'subject' => $this->subject
        ];
    }
}
