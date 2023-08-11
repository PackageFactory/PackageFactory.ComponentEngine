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

use LogicException;
use PackageFactory\ComponentEngine\Framework\PHP\Singleton\Singleton;
use PackageFactory\ComponentEngine\Language\AST\Node\IntegerLiteral\IntegerFormat;
use PackageFactory\ComponentEngine\Language\AST\Node\IntegerLiteral\IntegerLiteralNode;
use PackageFactory\ComponentEngine\Language\Lexer\Lexer;
use PackageFactory\ComponentEngine\Language\Lexer\LexerException;
use PackageFactory\ComponentEngine\Language\Lexer\Token\Token;
use PackageFactory\ComponentEngine\Language\Lexer\Token\TokenType;
use PackageFactory\ComponentEngine\Language\Lexer\Token\TokenTypes;
use PackageFactory\ComponentEngine\Language\Util\DebugHelper;

final class IntegerLiteralParser
{
    use Singleton;

    private static TokenTypes $INTEGER_TOKEN_TYPES;

    private function __construct()
    {
        self::$INTEGER_TOKEN_TYPES ??= TokenTypes::from(
            TokenType::INTEGER_HEXADECIMAL,
            TokenType::INTEGER_DECIMAL,
            TokenType::INTEGER_OCTAL,
            TokenType::INTEGER_BINARY
        );
    }

    public function parse(Lexer $lexer): IntegerLiteralNode
    {
        try {
            $lexer->readOneOf(self::$INTEGER_TOKEN_TYPES);
            $token = $lexer->getTokenUnderCursor();

            return new IntegerLiteralNode(
                rangeInSource: $token->rangeInSource,
                format: $this->getIntegerFormatFromToken($token),
                value: $token->value
            );
        } catch (LexerException $e) {
            throw IntegerLiteralCouldNotBeParsed::becauseOfLexerException($e);
        }
    }

    private function getIntegerFormatFromToken(Token $token): IntegerFormat
    {
        return match ($token->type) {
            TokenType::INTEGER_BINARY => IntegerFormat::BINARY,
            TokenType::INTEGER_OCTAL => IntegerFormat::OCTAL,
            TokenType::INTEGER_DECIMAL => IntegerFormat::DECIMAL,
            TokenType::INTEGER_HEXADECIMAL => IntegerFormat::HEXADECIMAL,
            default => throw new LogicException(
                sprintf(
                    'Expected %s to be one of %s',
                    $token->type->value,
                    DebugHelper::describeTokenTypes($this->INTEGER_TOKEN_TYPES)
                )
            )
        };
    }
}
