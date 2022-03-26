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
use PackageFactory\ComponentEngine\Parser\Ast\Spreadable;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;

final class Chain implements Spreadable, Term, Statement, Key, Child, \JsonSerializable
{
    /**
     * @param Token $start
     * @param Token $end
     * @param Term $root
     * @param array|ChainSegment[] $segments
     */
    private function __construct(
        public readonly Token $start,
        public readonly Token $end,
        public readonly Term $root,
        public readonly array $segments
    ) {
    }

    public static function fromTokenStream(
        Term $root,
        TokenStream $stream
    ): self {
        $start = $stream->current();
        $end = $start;

        $segments = [];
        $optional = false;
        while ($stream->valid()) {
            $stream->skipWhiteSpaceAndComments();
            if (!$stream->valid()) {
                break;
            }

            switch ($stream->current()->type) {
                case TokenType::OPERATOR_OPTCHAIN:
                    $end = $stream->current();
                    $optional = true;
                    $stream->next();
                    break;
                case TokenType::PERIOD:
                    $end = $stream->current();
                    $optional = false;
                    $stream->next();
                    break;
                case TokenType::BRACKETS_SQUARE_OPEN:
                case TokenType::BRACKETS_ROUND_OPEN:
                    $optional = false;
                    break;
                default:
                    break 2;
            }

            $stream->skipWhiteSpaceAndComments();

            switch ($stream->current()->type) {
                case TokenType::BRACKETS_SQUARE_OPEN:
                    $end = $stream->current();
                    $stream->next();

                    $token = $stream->current();
                    $key = ExpressionParser::parseTerm($stream);
                    if ($key instanceof Key) {
                        $segments[] = ChainSegment::fromKey($optional, $key);
                        $stream->skipWhiteSpaceAndComments();
                        $stream->consume(TokenType::BRACKETS_SQUARE_CLOSE);
                    } else {
                        throw ParserFailed::becauseOfUnexpectedTerm(
                            $token,
                            $key,
                            [
                                Identifier::class,
                                StringLiteral::class,
                                NumberLiteral::class,
                                TemplateLiteral::class,
                                Chain::class,
                                DashOperation::class
                            ]
                        );
                    }
                    break;

                case TokenType::BRACKETS_ROUND_OPEN:
                    $end = $stream->current();
                    $segment = array_pop($segments);
                    $segments[] = $segment->withCall(
                        Call::fromTokenStream($stream)
                    );
                    break;

                case TokenType::IDENTIFIER:
                    $end = $stream->current();
                    $segments[] = ChainSegment::fromKey(
                        $optional,
                        Identifier::fromTokenStream($stream)
                    );
                    break;

                default:
                    throw ParserFailed::becauseOfUnexpectedToken(
                        $stream->current(),
                        [
                            TokenType::BRACKETS_SQUARE_OPEN,
                            TokenType::BRACKETS_ROUND_OPEN,
                            TokenType::IDENTIFIER
                        ]
                    );
            }
        }

        return new self(
            start: $start,
            end: $end,
            root: $root,
            segments: $segments
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => 'Chain',
            'offset' => [
                $this->start->start->index,
                $this->end->end->index
            ],
            'root' => $this->root,
            'segments' => $this->segments
        ];
    }
}
