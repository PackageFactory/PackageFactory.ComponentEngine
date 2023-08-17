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
use PackageFactory\ComponentEngine\Language\Lexer\Matcher\Optional\Optional;
use PackageFactory\ComponentEngine\Language\Lexer\Matcher\Sequence\Sequence;
use PackageFactory\ComponentEngine\Language\Lexer\Rule\Rule;

abstract class Matcher
{
    /**
     * @var array<string,self>
     */
    private static $instancesByRule = [];

    final public static function for(Rule $rule): self
    {
        return self::$instancesByRule[$rule->value] ??= match ($rule) {
            Rule::COMMENT =>
                new Sequence(
                    new Exact('#'),
                    new Optional(new Not(new Exact("\n")))
                ),

            Rule::KEYWORD_FROM =>
                new Exact('from'),
            Rule::KEYWORD_IMPORT =>
                new Exact('import'),
            Rule::KEYWORD_EXPORT =>
                new Exact('export'),
            Rule::KEYWORD_ENUM =>
                new Exact('enum'),
            Rule::KEYWORD_STRUCT =>
                new Exact('struct'),
            Rule::KEYWORD_COMPONENT =>
                new Exact('component'),
            Rule::KEYWORD_MATCH =>
                new Exact('match'),
            Rule::KEYWORD_DEFAULT =>
                new Exact('default'),
            Rule::KEYWORD_RETURN =>
                new Exact('return'),
            Rule::KEYWORD_TRUE =>
                new Exact('true'),
            Rule::KEYWORD_FALSE =>
                new Exact('false'),
            Rule::KEYWORD_NULL =>
                new Exact('null'),

            Rule::STRING_LITERAL_DELIMITER =>
                new Exact('"'),
            Rule::STRING_LITERAL_CONTENT =>
                new Not(new Characters('"\\')),

            Rule::INTEGER_BINARY =>
                new Sequence(new Exact('0b'), new Characters('01')),
            Rule::INTEGER_OCTAL =>
                new Sequence(new Exact('0o'), new Characters('01234567')),
            Rule::INTEGER_DECIMAL =>
                new Characters('0123456789', 'box'),
            Rule::INTEGER_HEXADECIMAL =>
                new Sequence(new Exact('0x'), new Characters('0123456789ABCDEF')),

            Rule::TEMPLATE_LITERAL_DELIMITER =>
                new Exact('"""'),
            Rule::TEMPLATE_LITERAL_CONTENT =>
                new Not(new Characters('{}\\' . "\n")),

            Rule::ESCAPE_SEQUENCE_SINGLE_CHARACTER =>
                new Sequence(
                    new Exact('\\'),
                    new Fixed(1, new Characters('nrtvef\\$"'))
                ),
            Rule::ESCAPE_SEQUENCE_HEXADECIMAL =>
                new Sequence(
                    new Exact('\\x'),
                    new Fixed(2, new Characters('abcdefABCDEF0123456789'))
                ),
            Rule::ESCAPE_SEQUENCE_UNICODE =>
                new Sequence(
                    new Exact('\\u'),
                    new Fixed(4, new Characters('abcdefABCDEF0123456789'))
                ),
            Rule::ESCAPE_SEQUENCE_UNICODE_CODEPOINT =>
                new Sequence(
                    new Exact('\\u{'),
                    new Characters('abcdefABCDEF0123456789'),
                    new Exact('}')
                ),

            Rule::BRACKET_CURLY_OPEN =>
                new Exact('{'),
            Rule::BRACKET_CURLY_CLOSE =>
                new Exact('}'),
            Rule::BRACKET_ROUND_OPEN =>
                new Exact('('),
            Rule::BRACKET_ROUND_CLOSE =>
                new Exact(')'),
            Rule::BRACKET_SQUARE_OPEN =>
                new Exact('['),
            Rule::BRACKET_SQUARE_CLOSE =>
                new Exact(']'),
            Rule::BRACKET_ANGLE_OPEN =>
                new Exact('<'),
            Rule::BRACKET_ANGLE_CLOSE =>
                new Exact('>'),

            Rule::SYMBOL_COLON =>
                new Exact(':'),
            Rule::SYMBOL_PERIOD =>
                new Exact('.'),
            Rule::SYMBOL_QUESTIONMARK =>
                new Exact('?'),
            Rule::SYMBOL_EXCLAMATIONMARK =>
                new Exact('!'),
            Rule::SYMBOL_COMMA =>
                new Exact(','),
            Rule::SYMBOL_DASH =>
                new Exact('-'),
            Rule::SYMBOL_EQUALS =>
                new Exact('='),
            Rule::SYMBOL_SLASH_FORWARD =>
                new Exact('/'),
            Rule::SYMBOL_PIPE =>
                new Exact('|'),
            Rule::SYMBOL_BOOLEAN_AND =>
                new Exact('&&'),
            Rule::SYMBOL_BOOLEAN_OR =>
                new Exact('||'),
            Rule::SYMBOL_STRICT_EQUALS =>
                new Exact('==='),
            Rule::SYMBOL_NOT_EQUALS =>
                new Exact('!=='),
            Rule::SYMBOL_GREATER_THAN =>
                new Exact('>'),
            Rule::SYMBOL_GREATER_THAN_OR_EQUAL =>
                new Exact('>='),
            Rule::SYMBOL_LESS_THAN =>
                new Exact('<'),
            Rule::SYMBOL_LESS_THAN_OR_EQUAL =>
                new Exact('<='),
            Rule::SYMBOL_ARROW_SINGLE =>
                new Exact('->'),
            Rule::SYMBOL_OPTCHAIN =>
                new Exact('?.'),
            Rule::SYMBOL_NULLISH_COALESCE =>
                new Exact('??'),
            Rule::SYMBOL_CLOSE_TAG =>
                new Exact('</'),

            Rule::WORD =>
                new Characters(
                    'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
                ),
            Rule::TEXT =>
                new Not(new Characters('<{}>' . " \t\n")),

            Rule::SPACE =>
                new Characters(" \t"),
            Rule::END_OF_LINE =>
                new Exact("\n")
        };
    }

    abstract public function match(?string $character, int $offset): Result;
}
