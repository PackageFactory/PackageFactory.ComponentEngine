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

namespace PackageFactory\ComponentEngine\Language\Lexer\Rule;

use PackageFactory\ComponentEngine\Language\Lexer\Matcher\Characters\Characters;
use PackageFactory\ComponentEngine\Language\Lexer\Matcher\Exact\Exact;
use PackageFactory\ComponentEngine\Language\Lexer\Matcher\Fixed\Fixed;
use PackageFactory\ComponentEngine\Language\Lexer\Matcher\MatcherInterface;
use PackageFactory\ComponentEngine\Language\Lexer\Matcher\Not\Not;
use PackageFactory\ComponentEngine\Language\Lexer\Matcher\Optional\Optional;
use PackageFactory\ComponentEngine\Language\Lexer\Matcher\Sequence\Sequence;

enum Rule: string implements RuleInterface
{
    case COMMENT = 'COMMENT';

    case KEYWORD_FROM = 'KEYWORD_FROM';
    case KEYWORD_IMPORT = 'KEYWORD_IMPORT';
    case KEYWORD_EXPORT = 'KEYWORD_EXPORT';
    case KEYWORD_ENUM = 'KEYWORD_ENUM';
    case KEYWORD_STRUCT = 'KEYWORD_STRUCT';
    case KEYWORD_COMPONENT = 'KEYWORD_COMPONENT';
    case KEYWORD_MATCH = 'KEYWORD_MATCH';
    case KEYWORD_DEFAULT = 'KEYWORD_DEFAULT';
    case KEYWORD_RETURN = 'KEYWORD_RETURN';
    case KEYWORD_TRUE = 'KEYWORD_TRUE';
    case KEYWORD_FALSE = 'KEYWORD_FALSE';
    case KEYWORD_NULL = 'KEYWORD_NULL';

    case STRING_LITERAL_DELIMITER = 'STRING_LITERAL_DELIMITER';
    case STRING_LITERAL_CONTENT = 'STRING_LITERAL_CONTENT';

    case INTEGER_BINARY = 'INTEGER_BINARY';
    case INTEGER_OCTAL = 'INTEGER_OCTAL';
    case INTEGER_DECIMAL = 'INTEGER_DECIMAL';
    case INTEGER_HEXADECIMAL = 'INTEGER_HEXADECIMAL';

    case TEMPLATE_LITERAL_DELIMITER = 'TEMPLATE_LITERAL_DELIMITER';
    case TEMPLATE_LITERAL_CONTENT = 'TEMPLATE_LITERAL_CONTENT';

    case ESCAPE_SEQUENCE_SINGLE_CHARACTER = 'ESCAPE_SEQUENCE_SINGLE_CHARACTER';
    case ESCAPE_SEQUENCE_HEXADECIMAL = 'ESCAPE_SEQUENCE_HEXADECIMAL';
    case ESCAPE_SEQUENCE_UNICODE = 'ESCAPE_SEQUENCE_UNICODE';
    case ESCAPE_SEQUENCE_UNICODE_CODEPOINT = 'ESCAPE_SEQUENCE_UNICODE_CODEPOINT';

    case BRACKET_CURLY_OPEN = 'BRACKET_CURLY_OPEN';
    case BRACKET_CURLY_CLOSE = 'BRACKET_CURLY_CLOSE';
    case BRACKET_ROUND_OPEN = 'BRACKET_ROUND_OPEN';
    case BRACKET_ROUND_CLOSE = 'BRACKET_ROUND_CLOSE';
    case BRACKET_SQUARE_OPEN = 'BRACKET_SQUARE_OPEN';
    case BRACKET_SQUARE_CLOSE = 'BRACKET_SQUARE_CLOSE';
    case BRACKET_ANGLE_OPEN = 'BRACKET_ANGLE_OPEN';
    case BRACKET_ANGLE_CLOSE = 'BRACKET_ANGLE_CLOSE';

    case SYMBOL_PERIOD = 'SYMBOL_PERIOD';
    case SYMBOL_COLON = 'SYMBOL_COLON';
    case SYMBOL_QUESTIONMARK = 'SYMBOL_QUESTIONMARK';
    case SYMBOL_EXCLAMATIONMARK = 'SYMBOL_EXCLAMATIONMARK';
    case SYMBOL_COMMA = 'SYMBOL_COMMA';
    case SYMBOL_DASH = 'SYMBOL_DASH';
    case SYMBOL_EQUALS = 'SYMBOL_EQUALS';
    case SYMBOL_SLASH_FORWARD = 'SYMBOL_SLASH_FORWARD';
    case SYMBOL_PIPE = 'SYMBOL_PIPE';
    case SYMBOL_BOOLEAN_AND = 'SYMBOL_BOOLEAN_AND';
    case SYMBOL_BOOLEAN_OR = 'SYMBOL_BOOLEAN_OR';
    case SYMBOL_STRICT_EQUALS = 'SYMBOL_STRICT_EQUALS';
    case SYMBOL_NOT_EQUALS = 'SYMBOL_NOT_EQUALS';
    case SYMBOL_GREATER_THAN = 'SYMBOL_GREATER_THAN';
    case SYMBOL_GREATER_THAN_OR_EQUAL = 'SYMBOL_GREATER_THAN_OR_EQUAL';
    case SYMBOL_LESS_THAN = 'SYMBOL_LESS_THAN';
    case SYMBOL_LESS_THAN_OR_EQUAL = 'SYMBOL_LESS_THAN_OR_EQUAL';
    case SYMBOL_ARROW_SINGLE = 'SYMBOL_ARROW_SINGLE';
    case SYMBOL_OPTCHAIN = 'SYMBOL_OPTCHAIN';
    case SYMBOL_NULLISH_COALESCE = 'SYMBOL_NULLISH_COALESCE';
    case SYMBOL_CLOSE_TAG = 'SYMBOL_CLOSE_TAG';

    case WORD = 'WORD';
    case TEXT = 'TEXT';

    case SPACE = 'SPACE';
    case END_OF_LINE = 'END_OF_LINE';

    public function getMatcher(): MatcherInterface
    {
        return match ($this) {
            self::COMMENT =>
                new Sequence(
                    new Exact('#'),
                    new Optional(new Not(new Exact("\n")))
                ),

            self::KEYWORD_FROM =>
                new Exact('from'),
            self::KEYWORD_IMPORT =>
                new Exact('import'),
            self::KEYWORD_EXPORT =>
                new Exact('export'),
            self::KEYWORD_ENUM =>
                new Exact('enum'),
            self::KEYWORD_STRUCT =>
                new Exact('struct'),
            self::KEYWORD_COMPONENT =>
                new Exact('component'),
            self::KEYWORD_MATCH =>
                new Exact('match'),
            self::KEYWORD_DEFAULT =>
                new Exact('default'),
            self::KEYWORD_RETURN =>
                new Exact('return'),
            self::KEYWORD_TRUE =>
                new Exact('true'),
            self::KEYWORD_FALSE =>
                new Exact('false'),
            self::KEYWORD_NULL =>
                new Exact('null'),

            self::STRING_LITERAL_DELIMITER =>
                new Exact('"'),
            self::STRING_LITERAL_CONTENT =>
                new Not(new Characters('"\\')),

            self::INTEGER_BINARY =>
                new Sequence(new Exact('0b'), new Characters('01')),
            self::INTEGER_OCTAL =>
                new Sequence(new Exact('0o'), new Characters('01234567')),
            self::INTEGER_DECIMAL =>
                new Characters('0123456789', 'box'),
            self::INTEGER_HEXADECIMAL =>
                new Sequence(new Exact('0x'), new Characters('0123456789ABCDEF')),

            self::TEMPLATE_LITERAL_DELIMITER =>
                new Exact('"""'),
            self::TEMPLATE_LITERAL_CONTENT =>
                new Not(new Characters('{}\\' . "\n")),

            self::ESCAPE_SEQUENCE_SINGLE_CHARACTER =>
                new Sequence(
                    new Exact('\\'),
                    new Fixed(1, new Characters('nrtvef\\$"'))
                ),
            self::ESCAPE_SEQUENCE_HEXADECIMAL =>
                new Sequence(
                    new Exact('\\x'),
                    new Fixed(2, new Characters('abcdefABCDEF0123456789'))
                ),
            self::ESCAPE_SEQUENCE_UNICODE =>
                new Sequence(
                    new Exact('\\u'),
                    new Fixed(4, new Characters('abcdefABCDEF0123456789'))
                ),
            self::ESCAPE_SEQUENCE_UNICODE_CODEPOINT =>
                new Sequence(
                    new Exact('\\u{'),
                    new Characters('abcdefABCDEF0123456789'),
                    new Exact('}')
                ),

            self::BRACKET_CURLY_OPEN =>
                new Exact('{'),
            self::BRACKET_CURLY_CLOSE =>
                new Exact('}'),
            self::BRACKET_ROUND_OPEN =>
                new Exact('('),
            self::BRACKET_ROUND_CLOSE =>
                new Exact(')'),
            self::BRACKET_SQUARE_OPEN =>
                new Exact('['),
            self::BRACKET_SQUARE_CLOSE =>
                new Exact(']'),
            self::BRACKET_ANGLE_OPEN =>
                new Exact('<'),
            self::BRACKET_ANGLE_CLOSE =>
                new Exact('>'),

            self::SYMBOL_COLON =>
                new Exact(':'),
            self::SYMBOL_PERIOD =>
                new Exact('.'),
            self::SYMBOL_QUESTIONMARK =>
                new Exact('?'),
            self::SYMBOL_EXCLAMATIONMARK =>
                new Exact('!'),
            self::SYMBOL_COMMA =>
                new Exact(','),
            self::SYMBOL_DASH =>
                new Exact('-'),
            self::SYMBOL_EQUALS =>
                new Exact('='),
            self::SYMBOL_SLASH_FORWARD =>
                new Exact('/'),
            self::SYMBOL_PIPE =>
                new Exact('|'),
            self::SYMBOL_BOOLEAN_AND =>
                new Exact('&&'),
            self::SYMBOL_BOOLEAN_OR =>
                new Exact('||'),
            self::SYMBOL_STRICT_EQUALS =>
                new Exact('==='),
            self::SYMBOL_NOT_EQUALS =>
                new Exact('!=='),
            self::SYMBOL_GREATER_THAN =>
                new Exact('>'),
            self::SYMBOL_GREATER_THAN_OR_EQUAL =>
                new Exact('>='),
            self::SYMBOL_LESS_THAN =>
                new Exact('<'),
            self::SYMBOL_LESS_THAN_OR_EQUAL =>
                new Exact('<='),
            self::SYMBOL_ARROW_SINGLE =>
                new Exact('->'),
            self::SYMBOL_OPTCHAIN =>
                new Exact('?.'),
            self::SYMBOL_NULLISH_COALESCE =>
                new Exact('??'),
            self::SYMBOL_CLOSE_TAG =>
                new Exact('</'),

            self::WORD =>
                new Characters(
                    'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
                ),
            self::TEXT =>
                new Not(new Characters('<{}>' . " \t\n")),

            self::SPACE =>
                new Characters(" \t"),
            self::END_OF_LINE =>
                new Exact("\n")
        };
    }
}
