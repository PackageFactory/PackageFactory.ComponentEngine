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

namespace PackageFactory\ComponentEngine\Parser\Lexer;

enum TokenType
{
    case IDENTIFIER;
    case NUMBER;
    case MODULE_KEYWORD_IMPORT;
    case MODULE_KEYWORD_FROM;
    case MODULE_KEYWORD_AS;
    case MODULE_KEYWORD_CONST;
    case MODULE_KEYWORD_EXPORT;
    case MODULE_KEYWORD_DEFAULT;
    case MODULE_ASSIGNMENT;
    case MODULE_WILDCARD;
    case MODULE_AFX_START;
    case MODULE_AFX_END;
    case AFX_TAG_START;
    case AFX_TAG_END;
    case AFX_TAG_CLOSE;
    case AFX_TAG_CONTENT;
    case AFX_ATTRIBUTE_ASSIGNMENT;
    case AFX_EXPRESSION_START;
    case AFX_EXPRESSION_END;
    case AFX_EMBED_START;
    case AFX_EMBED_END;
    case KEYWORD_TRUE;
    case KEYWORD_FALSE;
    case KEYWORD_NULL;
    case OPERATOR_LOGICAL_NOT;
    case OPERATOR_LOGICAL_AND;
    case OPERATOR_LOGICAL_OR;
    case OPERATOR_SPREAD;
    case OPERATOR_ADD;
    case OPERATOR_SUBTRACT;
    case OPERATOR_MULTIPLY;
    case OPERATOR_DIVIDE;
    case OPERATOR_MODULO;
    case OPERATOR_OPTCHAIN;
    case OPERATOR_NULLISH_COALESCE;
    case COMPARATOR_EQ;
    case COMPARATOR_NEQ;
    case COMPARATOR_GT;
    case COMPARATOR_GTE;
    case COMPARATOR_LT;
    case COMPARATOR_LTE;
    case ARROW;
    case STRING_LITERAL_START;
    case STRING_LITERAL_CONTENT;
    case STRING_LITERAL_ESCAPE;
    case STRING_LITERAL_ESCAPED_CHARACTER;
    case STRING_LITERAL_END;
    case TEMPLATE_LITERAL_START;
    case TEMPLATE_LITERAL_CONTENT;
    case TEMPLATE_LITERAL_ESCAPE;
    case TEMPLATE_LITERAL_ESCAPED_CHARACTER;
    case TEMPLATE_LITERAL_INTERPOLATION_START;
    case TEMPLATE_LITERAL_INTERPOLATION_END;
    case TEMPLATE_LITERAL_END;
    case BRACKETS_ROUND_OPEN;
    case BRACKETS_ROUND_CLOSE;
    case BRACKETS_SQUARE_OPEN;
    case BRACKETS_SQUARE_CLOSE;
    case BRACKETS_CURLY_OPEN;
    case BRACKETS_CURLY_CLOSE;
    case PERIOD;
    case COLON;
    case QUESTIONMARK;
    case COMMA;
    case COMMENT_START;
    case COMMENT_CONTENT;
    case COMMENT_END;
    case WHITESPACE;
    case END_OF_LINE;
}
