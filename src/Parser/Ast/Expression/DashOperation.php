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
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;

final class DashOperation implements Term, Statement, Key, Child, \JsonSerializable
{
    const OPERATOR_ADD = '+';
    const OPERATOR_SUBTRACT = '-';

    private function __construct(
        public readonly Term $left,
        public readonly string $operator,
        public readonly Term $right
    ) {
        if ($operator !== self::OPERATOR_ADD && $operator !== self::OPERATOR_SUBTRACT) {
            throw new \Exception('@TODO: Unknown Operator');
        }
    }

    public static function fromTokenStream(Term $left, TokenStream $stream): self
    {
        $operator = null;
        switch ($stream->current()->type) {
            case TokenType::OPERATOR_ADD:
            case TokenType::OPERATOR_SUBTRACT:
                $operator = $stream->current()->value;
                $stream->next();
                break;
            default:
                throw ParserFailed::becauseOfUnexpectedToken(
                    $stream->current(),
                    [
                        TokenType::OPERATOR_ADD,
                        TokenType::OPERATOR_SUBTRACT
                    ]
                );
        }

        $stream->skipWhiteSpaceAndComments();

        return new self(
            left: $left,
            operator: $operator,
            right: ExpressionParser::parseTerm($stream,  ExpressionParser::PRIORITY_DASH_OPERATION)
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => 'DashOperation',
            'left' => $this->left,
            'operator' => $this->operator,
            'right' => $this->right
        ];
    }
}
