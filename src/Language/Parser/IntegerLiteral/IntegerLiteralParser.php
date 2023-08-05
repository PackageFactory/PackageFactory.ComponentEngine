<?php

/**
 * PackageFactory.ComponentEngine - Universal View Components for PHP
 *   Copyright (C) 2023 Contributors of PackageFactory.ComponentEngine
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

namespace PackageFactory\ComponentEngine\Language\Parser\IntegerLiteral;

use PackageFactory\ComponentEngine\Language\AST\Node\IntegerLiteral\IntegerFormat;
use PackageFactory\ComponentEngine\Language\AST\Node\IntegerLiteral\IntegerLiteralNode;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenTypes;

final class IntegerLiteralParser
{
    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return IntegerLiteralNode
     */
    public function parse(\Iterator &$tokens): IntegerLiteralNode
    {
        if (Scanner::isEnd($tokens)) {
            throw IntegerLiteralCouldNotBeParsed::becauseOfUnexpectedEndOfFile();
        }

        $token = $tokens->current();

        Scanner::skipOne($tokens);

        return new IntegerLiteralNode(
            rangeInSource: $token->boundaries,
            format: $this->getIntegerFormatFromToken($token),
            value: $token->value
        );
    }

    private function getIntegerFormatFromToken(Token $token): IntegerFormat
    {
        return match ($token->type) {
            TokenType::NUMBER_BINARY => IntegerFormat::BINARY,
            TokenType::NUMBER_OCTAL => IntegerFormat::OCTAL,
            TokenType::NUMBER_DECIMAL => IntegerFormat::DECIMAL,
            TokenType::NUMBER_HEXADECIMAL => IntegerFormat::HEXADECIMAL,

            default => throw IntegerLiteralCouldNotBeParsed::becauseOfUnexpectedToken(
                expectedTokenTypes: TokenTypes::from(
                    TokenType::NUMBER_BINARY,
                    TokenType::NUMBER_OCTAL,
                    TokenType::NUMBER_DECIMAL,
                    TokenType::NUMBER_HEXADECIMAL
                ),
                actualToken: $token
            )
        };
    }
}
