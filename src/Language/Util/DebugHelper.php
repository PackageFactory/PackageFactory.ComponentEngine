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

namespace PackageFactory\ComponentEngine\Language\Util;

use PackageFactory\ComponentEngine\Language\Lexer\Token\Token;
use PackageFactory\ComponentEngine\Language\Lexer\Token\TokenType;
use PackageFactory\ComponentEngine\Language\Lexer\Token\TokenTypes;

final class DebugHelper
{
    public static function describeTokenType(TokenType $tokenType): string
    {
        return $tokenType->value . match ($tokenType) {
            TokenType::COMMENT => ' (e.g. "# ...")',

            TokenType::KEYWORD_FROM => ' ("from")',
            TokenType::KEYWORD_IMPORT => ' ("import")',
            TokenType::KEYWORD_EXPORT => ' ("export")',
            TokenType::KEYWORD_ENUM => ' ("enum")',
            TokenType::KEYWORD_STRUCT => ' ("struct")',
            TokenType::KEYWORD_COMPONENT => ' ("component")',
            TokenType::KEYWORD_MATCH => ' ("match")',
            TokenType::KEYWORD_DEFAULT => ' ("default")',
            TokenType::KEYWORD_RETURN => ' ("return")',
            TokenType::KEYWORD_TRUE => ' ("true")',
            TokenType::KEYWORD_FALSE => ' ("false")',
            TokenType::KEYWORD_NULL => ' ("null")',

            TokenType::STRING_LITERAL_DELIMITER => ' (""")',
            TokenType::STRING_LITERAL_CONTENT => '',

            TokenType::INTEGER_BINARY => ' (e.g. "0b1001")',
            TokenType::INTEGER_OCTAL => ' (e.g. "0o644")',
            TokenType::INTEGER_DECIMAL => ' (e.g. "42")',
            TokenType::INTEGER_HEXADECIMAL => ' (e.g. "0xABC")',

            TokenType::TEMPLATE_LITERAL_DELIMITER => ' (""""")',
            TokenType::TEMPLATE_LITERAL_CONTENT => '',

            TokenType::ESCAPE_SEQUENCE_SINGLE_CHARACTER => ' (e.g. "\\\\" or "\\n")',
            TokenType::ESCAPE_SEQUENCE_HEXADECIMAL => ' (e.g. "\\xA9")',
            TokenType::ESCAPE_SEQUENCE_UNICODE => ' (e.g. "\\u00A9")',
            TokenType::ESCAPE_SEQUENCE_UNICODE_CODEPOINT => ' (e.g. "\\u{2F804}")',

            TokenType::BRACKET_CURLY_OPEN => ' ("{")',
            TokenType::BRACKET_CURLY_CLOSE => ' ("}")',
            TokenType::BRACKET_ROUND_OPEN => ' ("(")',
            TokenType::BRACKET_ROUND_CLOSE => ' (")")',
            TokenType::BRACKET_SQUARE_OPEN => ' ("[")',
            TokenType::BRACKET_SQUARE_CLOSE => ' ("]")',
            TokenType::BRACKET_ANGLE_OPEN => ' ("<")',
            TokenType::BRACKET_ANGLE_CLOSE => ' (">")',

            TokenType::SYMBOL_PERIOD => ' (".")',
            TokenType::SYMBOL_COLON => ' (":")',
            TokenType::SYMBOL_QUESTIONMARK => ' ("?")',
            TokenType::SYMBOL_EXCLAMATIONMARK => ' ("!")',
            TokenType::SYMBOL_COMMA => ' (",")',
            TokenType::SYMBOL_DASH => ' ("-")',
            TokenType::SYMBOL_EQUALS => ' ("=")',
            TokenType::SYMBOL_SLASH_FORWARD => ' ("/")',
            TokenType::SYMBOL_PIPE => ' ("|")',
            TokenType::SYMBOL_BOOLEAN_AND => ' ("&&")',
            TokenType::SYMBOL_BOOLEAN_OR => ' ("||")',
            TokenType::SYMBOL_STRICT_EQUALs => ' ("===")',
            TokenType::SYMBOL_NOT_EQUALs => ' ("!==")',
            TokenType::SYMBOL_GREATER_THAN_OR_EQUAL => ' (">=")',
            TokenType::SYMBOL_LESS_THAN_OR_EQUAL => ' ("<=")',
            TokenType::SYMBOL_ARROW_SINGLE => ' ("->")',
            TokenType::SYMBOL_OPTCHAIN => ' ("?.")',
            TokenType::SYMBOL_NULLISH_COALESCE => ' ("??")',

            TokenType::WORD => '',
            TokenType::TEXT => '',

            TokenType::SPACE => '',
            TokenType::END_OF_LINE => ''
        };
    }

    public static function describeTokenTypes(TokenTypes $tokenTypes): string
    {
        if (count($tokenTypes->items) === 1) {
            return self::describeTokenType($tokenTypes->items[0]);
        }

        $leadingItems = array_slice($tokenTypes->items, 0, -1);
        $trailingItem = array_slice($tokenTypes->items, -1)[0];

        return join(', ', array_map(
            static fn (TokenType $tokenType) => self::describeTokenType($tokenType),
            $leadingItems
        )) . ' or ' . self::describeTokenType($trailingItem);
    }

    public static function describeToken(Token $token): string
    {
        return sprintf('%s ("%s")', $token->type->value, $token->value);
    }
}
