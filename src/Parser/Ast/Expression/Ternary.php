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

use PackageFactory\ComponentEngine\Parser\Ast\Child;
use PackageFactory\ComponentEngine\Parser\Ast\Spreadable;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;

final class Ternary implements Spreadable, Term, Statement, Child, \JsonSerializable
{
    private function __construct(
        public readonly Term $condition,
        public readonly Term $trueBranch,
        public readonly Term $falseBranch
    ) {
    }

    public static function fromTokenStream(Term $condition, TokenStream $stream): self
    {
        $stream->consume(TokenType::QUESTIONMARK);
        $stream->skipWhiteSpaceAndComments();

        $trueBranch = ExpressionParser::parseTerm($stream);

        $stream->skipWhiteSpaceAndComments();
        $stream->consume(TokenType::COLON);
        $stream->skipWhiteSpaceAndComments();

        $falseBranch = ExpressionParser::parseTerm($stream);

        return new self(
            condition: $condition,
            trueBranch: $trueBranch,
            falseBranch: $falseBranch
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => 'Ternary',
            'condition' => $this->condition,
            'trueBranch' => $this->trueBranch,
            'falseBranch' => $this->falseBranch
        ];
    }
}
