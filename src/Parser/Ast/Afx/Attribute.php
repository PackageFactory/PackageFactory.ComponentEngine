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

namespace PackageFactory\ComponentEngine\Parser\Ast\Afx;

use PackageFactory\ComponentEngine\Exception\ParserFailed;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\StringLiteral;
use PackageFactory\ComponentEngine\Parser\Ast\ParameterAssignment;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;

final class Attribute implements ParameterAssignment, \JsonSerializable
{
    private function __construct(
        public readonly AttributeName $attributeName,
        public readonly ?Term $value
    ) {
    }

    public static function fromTokenStream(TokenStream $stream): self
    {
        $attributeName = AttributeName::fromTokenStream($stream);

        if ($stream->current()->type === TokenType::AFX_ATTRIBUTE_ASSIGNMENT) {
            $stream->next();
        } else {
            return new self(
                attributeName: $attributeName,
                value: null
            );
        }

        switch ($stream->current()->type) {
            case TokenType::STRING_LITERAL_START:
                $value = StringLiteral::fromTokenStream($stream);
                break;
            case TokenType::AFX_EXPRESSION_START:
                $stream->next();
                $value = ExpressionParser::parseTerm($stream);
                $stream->consume(TokenType::AFX_EXPRESSION_END);
                break;
            default:
                throw ParserFailed::becauseOfUnexpectedToken(
                    $stream->current(),
                    [
                        TokenType::STRING_LITERAL_START,
                        TokenType::AFX_EXPRESSION_START
                    ]
                );
        }

        return new self(
            attributeName: $attributeName,
            value: $value
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'name' => $this->attributeName,
            'value' => $this->value
        ];
    }
}
