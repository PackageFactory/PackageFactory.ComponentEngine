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

enum Rule: string
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
}
