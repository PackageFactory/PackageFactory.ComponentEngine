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

final class ArrowFunction implements Term, Statement, \JsonSerializable
{
    /**
     * @param array|Identifier[] $parameters
     * @param Term $body
     */
    private function __construct(
        public readonly array $parameters,
        public readonly Term $body
    ) {
    }

    public static function fromTokenStream(
        ?Identifier $firstParameter,
        TokenStream $stream
    ): self {
        if ($firstParameter === null) {
            $parameters = [];
        } else {
            $parameters = [$firstParameter];
        }

        while ($stream->valid()) {
            $stream->skipWhiteSpaceAndComments();

            if ($stream->current()->type === TokenType::COMMA) {
                $stream->next();
                $stream->skipWhiteSpaceAndComments();
            } elseif ($stream->current()->type === TokenType::BRACKETS_ROUND_CLOSE) {
                $stream->next();
                break;
            } elseif ($stream->current()->type === TokenType::ARROW) {
                $stream->next();
                break;
            }

            switch ($stream->current()->type) {
                case TokenType::IDENTIFIER:
                    $parameters[] = Identifier::fromTokenStream($stream);
                    break;

                default:
                    throw ParserFailed::becauseOfUnexpectedToken(
                        $stream->current(),
                        [TokenType::IDENTIFIER]
                    );
            }
        }

        $stream->skipWhiteSpaceAndComments();

        return new self(
            parameters: $parameters,
            body: ExpressionParser::parseTerm($stream)
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => 'ArrowFunction',
            'parameters' => $this->parameters,
            'body' => $this->body
        ];
    }
}
