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
use PackageFactory\ComponentEngine\Parser\Ast\Literal;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;

final class TemplateLiteral implements Literal, Term, Statement, Key, Child, \JsonSerializable
{
    /**
     * @param Token $start
     * @param Token $end
     * @param array|(string|Term)[] $segments
     */
    private function __construct(
        public readonly Token $start,
        public readonly Token $end,
        public readonly array $segments
    ) {
    }

    public static function fromTokenStream(TokenStream $stream): self
    {
        $start = $stream->current();
        $stream->consume(TokenType::TEMPLATE_LITERAL_START);

        $segments = [];
        $string = '';
        while ($stream->valid()) {
            switch ($stream->current()->type) {
                case TokenType::TEMPLATE_LITERAL_CONTENT:
                    $string .= $stream->current()->value;
                    $stream->next();
                    break;
                case TokenType::TEMPLATE_LITERAL_ESCAPE:
                    $stream->next();
                    break;
                case TokenType::TEMPLATE_LITERAL_ESCAPED_CHARACTER:
                    $string .= $stream->current()->value;
                    $stream->next();
                    break;
                case TokenType::TEMPLATE_LITERAL_INTERPOLATION_START:
                    if (!empty($string)) {
                        $segments[] = $string;
                        $string = '';
                    }
                    $stream->next();
                    $segments[] = ExpressionParser::parseTerm(
                        $stream,
                        ExpressionParser::PRIORITY_TERNARY
                    );
                    $stream->consume(TokenType::TEMPLATE_LITERAL_INTERPOLATION_END);
                    break;

                case TokenType::TEMPLATE_LITERAL_END:
                    if (!empty($string)) {
                        $segments[] = $string;
                        $string = '';
                    }
                    $end = $stream->current();
                    $stream->next();
                    return new self($start, $end, $segments);

                default:
                    throw ParserFailed::becauseOfUnexpectedToken(
                        $stream->current(),
                        [
                            TokenType::TEMPLATE_LITERAL_CONTENT,
                            TokenType::TEMPLATE_LITERAL_ESCAPE,
                            TokenType::TEMPLATE_LITERAL_ESCAPED_CHARACTER,
                            TokenType::TEMPLATE_LITERAL_INTERPOLATION_START,
                            TokenType::TEMPLATE_LITERAL_END
                        ]
                    );
            }
        }

        throw ParserFailed::becauseOfUnexpectedEndOfFile($stream);
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => 'TemplateLiteral',
            'offset' => [
                $this->start->start->index,
                $this->end->end->index
            ],
            'segments' => $this->segments
        ];
    }
}
