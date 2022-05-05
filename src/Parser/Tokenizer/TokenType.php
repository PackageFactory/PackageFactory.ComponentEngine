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

enum TokenType
{
    case COMMENT;

    case KEYWORD_FROM;
    case KEYWORD_IMPORT;
    case KEYWORD_EXPORT;
    case KEYWORD_META;
    case KEYWORD_ENUM;
    case KEYWORD_INTERFACE;
    case KEYWORD_COMPONENT;
    case KEYWORD_MATCH;
    case KEYWORD_DEFAULT;
    case KEYWORD_RETURN;

    case CONSTANT;

    case STRING;
    case STRING_QUOTED;

    case NUMBER_BINARY;
    case NUMBER_OCTAL;
    case NUMBER_DECIMAL;
    case NUMBER_HEXADECIMAL;

    case TEMPLATE_LITERAL_START;
    case TEMPLATE_LITERAL_END;

    case OPERATOR_ARITHMETIC;
    case OPERATOR_BOOLEAN;

    case COMPARATOR;
    case SPREAD;
    case ARROW;

    case BRACKET_OPEN;
    case BRACKET_CLOSE;

    case TAG_START_OPENING;
    case TAG_START_CLOSING;
    case TAG_SELF_CLOSE;
    case TAG_END;

    case PERIOD;
    case COLON;
    case QUESTIONMARK;
    case COMMA;
    case EQUALS;
    case SLASH_FORWARD;
    case DOLLAR;

    case SPACE;
    case END_OF_LINE;

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
}
