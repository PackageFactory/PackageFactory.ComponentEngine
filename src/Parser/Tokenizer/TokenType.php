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

namespace PackageFactory\ComponentEngine\Parser\Tokenizer;

use PackageFactory\ComponentEngine\Parser\Source\Fragment;

enum TokenType: string
{
    case COMMENT = 'COMMENT';

    case KEYWORD_FROM = 'KEYWORD_FROM';
    case KEYWORD_IMPORT = 'KEYWORD_IMPORT';
    case KEYWORD_EXPORT = 'KEYWORD_EXPORT';
    case KEYWORD_META = 'KEYWORD_META';
    case KEYWORD_ENUM = 'KEYWORD_ENUM';
    case KEYWORD_INTERFACE = 'KEYWORD_INTERFACE';
    case KEYWORD_COMPONENT = 'KEYWORD_COMPONENT';
    case KEYWORD_MATCH = 'KEYWORD_MATCH';
    case KEYWORD_DEFAULT = 'KEYWORD_DEFAULT';
    case KEYWORD_RETURN = 'KEYWORD_RETURN';

    case CONSTANT = 'CONSTANT';

    case STRING = 'STRING';
    case STRING_QUOTED = 'STRING_QUOTED';

    case NUMBER_BINARY = 'NUMBER_BINARY';
    case NUMBER_OCTAL = 'NUMBER_OCTAL';
    case NUMBER_DECIMAL = 'NUMBER_DECIMAL';
    case NUMBER_HEXADECIMAL = 'NUMBER_HEXADECIMAL';

    case TEMPLATE_LITERAL_START = 'TEMPLATE_LITERAL_START';
    case TEMPLATE_LITERAL_END = 'TEMPLATE_LITERAL_END';

    case OPERATOR_ARITHMETIC = 'OPERATOR_ARITHMETIC';
    case OPERATOR_BOOLEAN = 'OPERATOR_BOOLEAN';

    case COMPARATOR = 'COMPARATOR';
    case SPREAD = 'SPREAD';
    case ARROW = 'ARROW';

    case BRACKET_CURLY_OPEN = 'BRACKET_CURLY_OPEN';
    case BRACKET_CURLY_CLOSE = 'BRACKET_CURLY_CLOSE';
    case BRACKET_ROUND_OPEN = 'BRACKET_ROUND_OPEN';
    case BRACKET_ROUND_CLOSE = 'BRACKET_ROUND_CLOSE';
    case BRACKET_SQUARE_OPEN = 'BRACKET_SQUARE_OPEN';
    case BRACKET_SQUARE_CLOSE = 'BRACKET_SQUARE_CLOSE';

    case TAG_START_OPENING = 'TAG_START_OPENING';
    case TAG_START_CLOSING = 'TAG_START_CLOSING';
    case TAG_SELF_CLOSE = 'TAG_SELF_CLOSE';
    case TAG_END = 'TAG_END';

    case PERIOD = 'PERIOD';
    case COLON = 'COLON';
    case QUESTIONMARK = 'QUESTIONMARK';
    case COMMA = 'COMMA';
    case EQUALS = 'EQUALS';
    case SLASH_FORWARD = 'SLASH_FORWARD';
    case DOLLAR = 'DOLLAR';

    case SPACE = 'SPACE';
    case END_OF_LINE = 'END_OF_LINE';

    public static function fromBuffer(Buffer $buffer): TokenType
    {
        $value = $buffer->value();

        return match (true) {
            $value === 'from' => self::KEYWORD_FROM,
            $value === 'import' => self::KEYWORD_IMPORT,
            $value === 'export' => self::KEYWORD_EXPORT,
            $value === 'meta' => self::KEYWORD_META,
            $value === 'enum' => self::KEYWORD_ENUM,
            $value === 'interface' => self::KEYWORD_INTERFACE,
            $value === 'component' => self::KEYWORD_COMPONENT,
            $value === 'match' => self::KEYWORD_MATCH,
            $value === 'default' => self::KEYWORD_DEFAULT,
            $value === 'return' => self::KEYWORD_RETURN,

            (bool) preg_match(
                '/^0[bB][0-1]+$/',
                $value
            ) => self::NUMBER_BINARY,
            (bool) preg_match(
                '/^0o[0-7]+$/',
                $value
            ) => self::NUMBER_OCTAL,
            (bool) preg_match(
                '/^([-+]?[0-9]+)?(\.[0-9]+)?([eE][0-9]+)?$/',
                $value
            ) => self::NUMBER_DECIMAL,
            (bool) preg_match(
                '/^0x[0-9a-fA-F]+$/',
                $value
            ) => self::NUMBER_HEXADECIMAL,
            default => self::STRING
        };
    }

    public static function tryBracketOpenFromFragment(Fragment $fragment): ?self
    {
        return match ($fragment->value) {
            '{' => self::BRACKET_CURLY_OPEN,
            '(' => self::BRACKET_ROUND_OPEN,
            '[' => self::BRACKET_SQUARE_OPEN,
            default => null
        };
    }

    public function closingBracket(): TokenType
    {
        return match ($this) {
            self::BRACKET_CURLY_OPEN => self::BRACKET_CURLY_CLOSE,
            self::BRACKET_ROUND_OPEN => self::BRACKET_ROUND_CLOSE,
            self::BRACKET_SQUARE_OPEN => self::BRACKET_SQUARE_CLOSE,
            default => throw new \Exception('@TODO: Not a bracket.')
        };
    }

    public function matchesString(string $string): bool
    {
        return match ($this) {
            self::BRACKET_CURLY_CLOSE => $string === '}',
            self::BRACKET_ROUND_CLOSE => $string === ')',
            self::BRACKET_SQUARE_CLOSE => $string === ']',
            default => false
        };
    }
}
