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
    case KEYWORD_ENUM = 'KEYWORD_ENUM';
    case KEYWORD_STRUCT = 'KEYWORD_STRUCT';
    case KEYWORD_COMPONENT = 'KEYWORD_COMPONENT';
    case KEYWORD_MATCH = 'KEYWORD_MATCH';
    case KEYWORD_DEFAULT = 'KEYWORD_DEFAULT';
    case KEYWORD_RETURN = 'KEYWORD_RETURN';
    case KEYWORD_TRUE = 'KEYWORD_TRUE';
    case KEYWORD_FALSE = 'KEYWORD_FALSE';
    case KEYWORD_NULL = 'KEYWORD_NULL';

    case CONSTANT = 'CONSTANT';

    case STRING = 'STRING';
    case STRING_QUOTED = 'STRING_QUOTED';

    case NUMBER_BINARY = 'NUMBER_BINARY';
    case NUMBER_OCTAL = 'NUMBER_OCTAL';
    case NUMBER_DECIMAL = 'NUMBER_DECIMAL';
    case NUMBER_HEXADECIMAL = 'NUMBER_HEXADECIMAL';

    case TEMPLATE_LITERAL_START = 'TEMPLATE_LITERAL_START';
    case TEMPLATE_LITERAL_END = 'TEMPLATE_LITERAL_END';

    case OPERATOR_BOOLEAN_AND = 'OPERATOR_BOOLEAN_AND';
    case OPERATOR_BOOLEAN_OR = 'OPERATOR_BOOLEAN_OR';
    case OPERATOR_BOOLEAN_NOT = 'OPERATOR_BOOLEAN_NOT';

    case COMPARATOR_EQUAL = 'COMPARATOR_EQUAL';
    case COMPARATOR_NOT_EQUAL = 'COMPARATOR_NOT_EQUAL';
    case COMPARATOR_GREATER_THAN = 'COMPARATOR_GREATER_THAN';
    case COMPARATOR_GREATER_THAN_OR_EQUAL = 'COMPARATOR_GREATER_THAN_OR_EQUAL';
    case COMPARATOR_LESS_THAN = 'COMPARATOR_LESS_THAN';
    case COMPARATOR_LESS_THAN_OR_EQUAL = 'COMPARATOR_LESS_THAN_OR_EQUAL';

    case ARROW_SINGLE = 'ARROW_SINGLE';

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
    case PIPE = 'PIPE';

    case OPTCHAIN = 'OPTCHAIN';
    case NULLISH_COALESCE = 'NULLISH_COALESCE';

    case SPACE = 'SPACE';
    case END_OF_LINE = 'END_OF_LINE';

    public static function fromBuffer(Buffer $buffer): TokenType
    {
        $value = $buffer->value();

        return match (true) {
            $value === 'from' => self::KEYWORD_FROM,
            $value === 'import' => self::KEYWORD_IMPORT,
            $value === 'export' => self::KEYWORD_EXPORT,
            $value === 'enum' => self::KEYWORD_ENUM,
            $value === 'struct' => self::KEYWORD_STRUCT,
            $value === 'component' => self::KEYWORD_COMPONENT,
            $value === 'match' => self::KEYWORD_MATCH,
            $value === 'default' => self::KEYWORD_DEFAULT,
            $value === 'return' => self::KEYWORD_RETURN,
            $value === 'true' => self::KEYWORD_TRUE,
            $value === 'false' => self::KEYWORD_FALSE,
            $value === 'null' => self::KEYWORD_NULL,

            $value === '.' => self::PERIOD,

            (bool) preg_match(
                '/^0[bB][0-1]+$/',
                $value
            ) => self::NUMBER_BINARY,
            (bool) preg_match(
                '/^0o[0-7]+$/',
                $value
            ) => self::NUMBER_OCTAL,
            $value !== '' && preg_match(
                '/^([-+]?[0-9]+)$/',
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

    public function toDebugString(): string
    {
        return $this->value . match ($this) {
            self::COMMENT => ' (e.g. "# ...")',
            self::KEYWORD_FROM => ' ("from")',
            self::KEYWORD_IMPORT => ' ("import")',
            self::KEYWORD_EXPORT => ' ("export")',
            self::KEYWORD_ENUM => ' ("enum")',
            self::KEYWORD_STRUCT => ' ("struct")',
            self::KEYWORD_COMPONENT => ' ("component")',
            self::KEYWORD_MATCH => ' ("match")',
            self::KEYWORD_DEFAULT => ' ("default")',
            self::KEYWORD_RETURN => ' ("return")',
            self::KEYWORD_TRUE => ' ("true")',
            self::KEYWORD_FALSE => ' ("false")',
            self::KEYWORD_NULL => ' ("null")',
            self::CONSTANT => '',
            self::STRING => '',
            self::STRING_QUOTED => '',
            self::NUMBER_BINARY => ' (e.g. "0b1001")',
            self::NUMBER_OCTAL => ' (e.g. "0o644")',
            self::NUMBER_DECIMAL => ' (e.g. "42")',
            self::NUMBER_HEXADECIMAL => ' (e.g. "0xABC")',
            self::TEMPLATE_LITERAL_START => ' ("`")',
            self::TEMPLATE_LITERAL_END => ' ("`")',
            self::OPERATOR_BOOLEAN_AND => ' ("&&")',
            self::OPERATOR_BOOLEAN_OR => ' ("||")',
            self::OPERATOR_BOOLEAN_NOT => ' ("!")',
            self::COMPARATOR_EQUAL => ' ("===")',
            self::COMPARATOR_NOT_EQUAL => ' ("!==")',
            self::COMPARATOR_GREATER_THAN => ' (">")',
            self::COMPARATOR_GREATER_THAN_OR_EQUAL => ' (">=")',
            self::COMPARATOR_LESS_THAN => ' ("<")',
            self::COMPARATOR_LESS_THAN_OR_EQUAL => ' ("<=")',
            self::ARROW_SINGLE => ' ("->")',
            self::BRACKET_CURLY_OPEN => ' ("{")',
            self::BRACKET_CURLY_CLOSE => ' ("}")',
            self::BRACKET_ROUND_OPEN => ' ("(")',
            self::BRACKET_ROUND_CLOSE => ' (")")',
            self::BRACKET_SQUARE_OPEN => ' ("[")',
            self::BRACKET_SQUARE_CLOSE => ' ("]")',
            self::TAG_START_OPENING => ' ("<")',
            self::TAG_START_CLOSING => ' ("</")',
            self::TAG_SELF_CLOSE => ' ("/>")',
            self::TAG_END => ' (">")',
            self::PERIOD => ' (".")',
            self::COLON => ' (":")',
            self::QUESTIONMARK => ' ("?")',
            self::COMMA => ' (",")',
            self::EQUALS => ' ("=")',
            self::SLASH_FORWARD => ' ("/")',
            self::DOLLAR => ' ("$")',
            self::PIPE => ' ("|")',
            self::OPTCHAIN => ' ("?.")',
            self::NULLISH_COALESCE => ' ("??")',
            self::SPACE => '',
            self::END_OF_LINE => ''
        };
    }
}
