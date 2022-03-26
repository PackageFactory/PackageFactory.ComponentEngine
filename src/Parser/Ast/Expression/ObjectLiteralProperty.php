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
use PackageFactory\ComponentEngine\Parser\Ast\Key;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;

final class ObjectLiteralProperty implements \JsonSerializable
{
    private function __construct(
        public readonly ?Key $key,
        public readonly Statement $value
    ) {
    }

    public static function fromTokenStream(TokenStream $stream): self
    {
        $key = null;
        switch ($stream->current()->type) {
            case TokenType::IDENTIFIER:
                $key = Identifier::fromTokenStream($stream);
                break;
            case TokenType::BRACKETS_SQUARE_OPEN:
                $stream->next();

                $token = $stream->current();
                $key = ExpressionParser::parseTerm($stream);
                if ($key instanceof Key) {
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
            case TokenType::OPERATOR_SPREAD:
                return new self(
                    key: null,
                    value: ExpressionParser::parseStatement($stream,  ExpressionParser::PRIORITY_LIST)
                );

            default:
                throw ParserFailed::becauseOfUnexpectedToken(
                    $stream->current(),
                    [
                        TokenType::IDENTIFIER,
                        TokenType::BRACKETS_SQUARE_OPEN,
                        TokenType::OPERATOR_SPREAD
                    ]
                );
        }

        $stream->skipWhiteSpaceAndComments();
        $stream->consume(TokenType::COLON);

        $stream->skipWhiteSpaceAndComments();

        /** @var Key $key */
        return new self(
            key: $key,
            value: ExpressionParser::parseStatement($stream, ExpressionParser::PRIORITY_LIST)
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
        ];
    }
}
