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

namespace PackageFactory\ComponentEngine\Parser\Ast\Module;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Identifier;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;

final class Constant implements \JsonSerializable
{
    private function __construct(
        public readonly Identifier $name,
        public readonly Term $value
    ) {
    }

    public static function fromTokenStream(TokenStream $stream): self
    {
        $start = $stream->current();
        $stream->consume(TokenType::MODULE_KEYWORD_CONST);

        $stream->skipWhiteSpaceAndComments();

        $name = Identifier::fromTokenStream($stream);

        $stream->skipWhiteSpaceAndComments();
        $stream->consume(TokenType::MODULE_ASSIGNMENT);

        $value = null;
        $brackets = 0;
        while ($value === null) {
            $stream->skipWhiteSpaceAndComments();

            switch ($stream->current()->type) {
                case TokenType::BRACKETS_ROUND_OPEN:
                    $brackets++;
                    $stream->next();
                    break;
                default:
                    $value = ExpressionParser::parseTerm($stream);
                    break;
            }
        }

        while ($brackets > 0) {
            $stream->skipWhiteSpaceAndComments();
            $stream->consume(TokenType::BRACKETS_ROUND_CLOSE);
            $brackets--;
        }

        return new self($name, $value);
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => 'Constant',
            'name' => $this->name,
            'value' => $this->value
        ];
    }
}
