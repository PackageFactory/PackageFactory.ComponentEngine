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
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;

final class Comparison implements Term, Statement, \JsonSerializable
{
    private function __construct(
        public readonly Term $left,
        public readonly Comparator $operator,
        public readonly Term $right
    ) {
    }

    public static function fromTokenStream(Term $left, TokenStream $stream): self
    {
        $operator = null;
        switch ($stream->current()->type) {
            case TokenType::COMPARATOR_EQ:
            case TokenType::COMPARATOR_NEQ:
            case TokenType::COMPARATOR_GT:
            case TokenType::COMPARATOR_GTE:
            case TokenType::COMPARATOR_LT:
            case TokenType::COMPARATOR_LTE:
                $operator =  Comparator::fromToken($stream->current());
                $stream->next();
                break;
            default:
                throw ParserFailed::becauseOfUnexpectedToken(
                    $stream->current(),
                    [
                        TokenType::COMPARATOR_EQ,
                        TokenType::COMPARATOR_GT,
                        TokenType::COMPARATOR_GTE,
                        TokenType::COMPARATOR_LT,
                        TokenType::COMPARATOR_LTE
                    ]
                );
        }

        $stream->skipWhiteSpaceAndComments();

        return new self(
            left: $left,
            operator: $operator,
            right: ExpressionParser::parseTerm($stream, ExpressionParser::PRIORITY_COMPARISON)
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => 'Comparison',
            'left' => $this->left,
            'operator' => $this->operator,
            'right' => $this->right
        ];
    }
}
