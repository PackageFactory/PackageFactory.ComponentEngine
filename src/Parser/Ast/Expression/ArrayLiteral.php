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
use PackageFactory\ComponentEngine\Parser\Ast\Literal;
use PackageFactory\ComponentEngine\Parser\Ast\Spreadable;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;

final class ArrayLiteral implements
    Literal,
    Spreadable,
    Term,
    Statement,
    Child,
    \JsonSerializable
{
    /**
     * @param Token $start
     * @param Token $end
     * @param array|Statement[] $items
     */
    private function __construct(
        public readonly Token $start,
        public readonly Token $end,
        public readonly array $items
    ) {
    }

    public static function fromTokenStream(TokenStream $stream): self
    {
        $start = $stream->current();
        $end = $stream->current();
        $stream->consume(TokenType::BRACKETS_SQUARE_OPEN);

        $items = [];
        while ($stream->valid()) {
            $stream->skipWhiteSpaceAndComments();

            switch ($stream->current()->type) {
                case TokenType::BRACKETS_SQUARE_CLOSE:
                    $end = $stream->current();
                    $stream->next();
                    break 2;

                default:
                    $items[] = ExpressionParser::parseStatement(
                        $stream,
                        ExpressionParser::PRIORITY_LIST
                    );
                    break;
            }

            $stream->skipWhiteSpaceAndComments();

            if ($stream->current()->type === TokenType::COMMA) {
                $stream->next();
            } else {
                $stream->skipWhiteSpaceAndComments();
                $end = $stream->consume(TokenType::BRACKETS_SQUARE_CLOSE);
                break;
            }
        }

        return new self(
            start: $start,
            end: $end,
            items: $items
        );
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'ArrayLiteral',
            'offset' => [
                $this->start->start->index,
                $this->end->end->index
            ],
            'items' => $this->items
        ];
    }
}
