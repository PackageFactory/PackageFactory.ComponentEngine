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

namespace PackageFactory\ComponentEngine\Language\Lexer\Matcher;

use PackageFactory\ComponentEngine\Language\Lexer\Matcher\Characters\Characters;
use PackageFactory\ComponentEngine\Language\Lexer\Matcher\Exact\Exact;
use PackageFactory\ComponentEngine\Language\Lexer\Matcher\Fixed\Fixed;
use PackageFactory\ComponentEngine\Language\Lexer\Matcher\Not\Not;
use PackageFactory\ComponentEngine\Language\Lexer\Matcher\Sequence\Sequence;
use PackageFactory\ComponentEngine\Language\Lexer\Token\TokenType;

abstract class Matcher
{
    /**
     * @var array<string,self>
     */
    private static $instancesByTokenType = [];

    final public static function for(TokenType $tokenType): self
    {
        return self::$instancesByTokenType[$tokenType->value] ??= match ($tokenType) {
            TokenType::COMMENT =>
                new Sequence(new Exact('#'), new Not(new Exact("\n"))),

            TokenType::KEYWORD_FROM =>
                new Exact('from'),
            TokenType::KEYWORD_IMPORT =>
                new Exact('import'),
            TokenType::KEYWORD_EXPORT =>
                new Exact('export'),
            TokenType::KEYWORD_ENUM =>
                new Exact('enum'),
            TokenType::KEYWORD_STRUCT =>
                new Exact('struct'),
            TokenType::KEYWORD_COMPONENT =>
                new Exact('component'),
            TokenType::KEYWORD_MATCH =>
                new Exact('match'),
            TokenType::KEYWORD_DEFAULT =>
                new Exact('default'),
            TokenType::KEYWORD_RETURN =>
                new Exact('return'),
            TokenType::KEYWORD_TRUE =>
                new Exact('true'),
            TokenType::KEYWORD_FALSE =>
                new Exact('false'),
            TokenType::KEYWORD_NULL =>
                new Exact('null'),

            TokenType::STRING_LITERAL_DELIMITER =>
                new Exact('"'),
            TokenType::STRING_LITERAL_CONTENT =>
                new Not(new Characters('"\\' . "\n")),

            TokenType::INTEGER_BINARY =>
                new Sequence(new Exact('0b'), new Characters('01')),
            TokenType::INTEGER_OCTAL =>
                new Sequence(new Exact('0o'), new Characters('01234567')),
            TokenType::INTEGER_DECIMAL =>
                new Characters('0123456789', 'box'),
            TokenType::INTEGER_HEXADECIMAL =>
                new Sequence(new Exact('0x'), new Characters('0123456789ABCDEF')),

            TokenType::TEMPLATE_LITERAL_DELIMITER =>
                new Exact('"""'),
            TokenType::TEMPLATE_LITERAL_CONTENT =>
                new Not(new Characters('{}\\' . "\n")),

            TokenType::ESCAPE_SEQUENCE_SINGLE_CHARACTER =>
                new Sequence(
                    new Exact('\\'),
                    new Fixed(1, new Characters('nrtvef\\$"'))
                ),
            TokenType::ESCAPE_SEQUENCE_HEXADECIMAL =>
                new Sequence(
                    new Exact('\\x'),
                    new Fixed(2, new Characters('abcdefABCDEF0123456789'))
                ),
            TokenType::ESCAPE_SEQUENCE_UNICODE =>
                new Sequence(
                    new Exact('\\u'),
                    new Fixed(4, new Characters('abcdefABCDEF0123456789'))
                ),
            TokenType::ESCAPE_SEQUENCE_UNICODE_CODEPOINT =>
                new Sequence(
                    new Exact('\\u{'),
                    new Characters('abcdefABCDEF0123456789'),
                    new Exact('}')
                ),

            TokenType::BRACKET_CURLY_OPEN =>
                new Exact('{'),
            TokenType::BRACKET_CURLY_CLOSE =>
                new Exact('}'),
            TokenType::BRACKET_ROUND_OPEN =>
                new Exact('('),
            TokenType::BRACKET_ROUND_CLOSE =>
                new Exact(')'),
            TokenType::BRACKET_SQUARE_OPEN =>
                new Exact('['),
            TokenType::BRACKET_SQUARE_CLOSE =>
                new Exact(']'),
            TokenType::BRACKET_ANGLE_OPEN =>
                new Exact('<'),
            TokenType::BRACKET_ANGLE_CLOSE =>
                new Exact('>'),

            TokenType::SYMBOL_COLON =>
                new Exact(':'),
            TokenType::SYMBOL_PERIOD =>
                new Exact('.'),
            TokenType::SYMBOL_QUESTIONMARK =>
                new Exact('?'),
            TokenType::SYMBOL_EXCLAMATIONMARK =>
                new Exact('!'),
            TokenType::SYMBOL_COMMA =>
                new Exact(','),
            TokenType::SYMBOL_DASH =>
                new Exact('-'),
            TokenType::SYMBOL_EQUALS =>
                new Exact('='),
            TokenType::SYMBOL_SLASH_FORWARD =>
                new Exact('/'),
            TokenType::SYMBOL_PIPE =>
                new Exact('|'),
            TokenType::SYMBOL_BOOLEAN_AND =>
                new Exact('&&'),
            TokenType::SYMBOL_BOOLEAN_OR =>
                new Exact('||'),
            TokenType::SYMBOL_STRICT_EQUALs =>
                new Exact('==='),
            TokenType::SYMBOL_NOT_EQUALs =>
                new Exact('!=='),
            TokenType::SYMBOL_GREATER_THAN_OR_EQUAL =>
                new Exact('>='),
            TokenType::SYMBOL_LESS_THAN_OR_EQUAL =>
                new Exact('<='),
            TokenType::SYMBOL_ARROW_SINGLE =>
                new Exact('->'),
            TokenType::SYMBOL_OPTCHAIN =>
                new Exact('?.'),
            TokenType::SYMBOL_NULLISH_COALESCE =>
                new Exact('??'),

            TokenType::WORD =>
                new Characters(
                    'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
                ),
            TokenType::TEXT =>
                new Not(new Characters('<{}>')),

            TokenType::SPACE =>
                new Characters(" \t"),
            TokenType::END_OF_LINE =>
                new Exact("\n")
        };
    }

    abstract public function match(?string $character, int $offset): Result;
}
