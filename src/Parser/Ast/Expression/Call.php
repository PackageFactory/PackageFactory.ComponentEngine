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

use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;

final class Call implements \JsonSerializable
{
    /**
     * @param array|Term[] $arguments
     */
    private function __construct(public readonly array $arguments)
    {
    }

    public static function fromTokenStream(TokenStream $stream): self
    {
        $stream->consume(TokenType::BRACKETS_ROUND_OPEN);

        $arguments = [];
        while ($stream->valid()) {
            $stream->skipWhiteSpaceAndComments();

            switch ($stream->current()->type) {
                case TokenType::BRACKETS_ROUND_CLOSE:
                    $stream->next();
                    return new self($arguments);

                default:
                    $arguments[] = ExpressionParser::parseTerm($stream);
                    break;
            }

            $stream->skipWhiteSpaceAndComments();

            if ($stream->current()->type === TokenType::COMMA) {
                $stream->next();
            } else {
                $stream->skipWhiteSpaceAndComments();
                $stream->consume(TokenType::BRACKETS_ROUND_CLOSE);
                break;
            }
        }

        return new self(arguments: $arguments);
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => 'Call',
            'arguments' => $this->arguments
        ];
    }
}
