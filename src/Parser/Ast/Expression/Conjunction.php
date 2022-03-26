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
use PackageFactory\ComponentEngine\Parser\Ast\Spreadable;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;

final class Conjunction implements Spreadable, Term, Statement, Child, \JsonSerializable
{
    private function __construct(
        private readonly Term $left,
        private readonly Term $right
    ) {
    }

    public static function fromTokenStream(Term $left, TokenStream $stream): self
    {
        $operator = null;
        switch ($stream->current()->type) {
            case TokenType::OPERATOR_LOGICAL_AND:
                $stream->next();
                break;
            default:
                throw ParserFailed::becauseOfUnexpectedToken(
                    $stream->current(),
                    [TokenType::OPERATOR_LOGICAL_AND]
                );
        }

        $stream->skipWhiteSpaceAndComments();

        return new self(
            left: $left,
            right: ExpressionParser::parseTerm($stream, ExpressionParser::PRIORITY_CONJUNCTION)
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => 'Conjunction',
            'left' => $this->left,
            'right' => $this->right
        ];
    }
}
