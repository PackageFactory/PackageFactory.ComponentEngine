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

use PackageFactory\ComponentEngine\Language\AST\Node\BinaryOperation\BinaryOperationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Expression\ExpressionNode;
use PackageFactory\ComponentEngine\Language\AST\Node\IntegerLiteral\IntegerLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Node;
use PackageFactory\ComponentEngine\Language\AST\Node\StringLiteral\StringLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\TagNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralExpressionSegmentNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralStringSegmentNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TernaryOperation\TernaryOperationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\ValueReference\ValueReferenceNode;
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
            TokenType::SYMBOL_STRICT_EQUALS => ' ("===")',
            TokenType::SYMBOL_NOT_EQUALS => ' ("!==")',
            TokenType::SYMBOL_GREATER_THAN => ' (">")',
            TokenType::SYMBOL_GREATER_THAN_OR_EQUAL => ' (">=")',
            TokenType::SYMBOL_LESS_THAN => ' ("<")',
            TokenType::SYMBOL_LESS_THAN_OR_EQUAL => ' ("<=")',
            TokenType::SYMBOL_ARROW_SINGLE => ' ("->")',
            TokenType::SYMBOL_OPTCHAIN => ' ("?.")',
            TokenType::SYMBOL_NULLISH_COALESCE => ' ("??")',
            TokenType::SYMBOL_CLOSE_TAG => ' ("</")',

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

    public static function printASTNode(Node $node, string $indentation = ''): string
    {
        return $indentation . match ($node::class) {
            BinaryOperationNode::class => self::printBinaryOperationNode($node, $indentation),
            ExpressionNode::class => self::printExpressionNode($node, $indentation),
            IntegerLiteralNode::class => self::printIntegerLiteralNode($node, $indentation),
            StringLiteralNode::class => self::printStringLiteralNode($node, $indentation),
            TagNode::class => self::printTagNode($node, $indentation),
            TemplateLiteralNode::class => self::printTemplateLiteralNode($node, $indentation),
            TernaryOperationNode::class => self::printTernaryOperationNode($node, $indentation),
            ValueReferenceNode::class => self::printValueReferenceNode($node, $indentation),
            default => throw new \Exception(__METHOD__ . ' is not implemented yet for: ' . $node::class)
        };
    }

    public static function printBinaryOperationNode(BinaryOperationNode $node, string $indentation = ''): string
    {
        $left = self::printASTNode($node->leftOperand, $indentation . '  ');
        $right = self::printASTNode($node->rightOperand, $indentation . '  ');
        $op = $indentation . '      ' . $node->operator->name;

        return $indentation . 'BinaryOperation' . PHP_EOL . $left . PHP_EOL . $op . PHP_EOL . $right;
    }

    public static function printExpressionNode(ExpressionNode $node, string $indentation = ''): string
    {
        return $indentation . 'Expression' . PHP_EOL . self::printASTNode($node->root, $indentation . '  ');
    }

    public static function printIntegerLiteralNode(IntegerLiteralNode $node, string $indentation = ''): string
    {
        return $indentation . 'IntegerLiteral (format=' . $node->format->name . ')' . $node->value;
    }

    public static function printStringLiteralNode(StringLiteralNode $node, string $indentation = ''): string
    {
        return $indentation . 'StringLiteral "' . substr(addslashes($node->value), 0, 64 - strlen($indentation)) .  '"';
    }

    public static function printTemplateLiteralNode(TemplateLiteralNode $node, string $indentation = ''): string
    {
        $lines = [];
        foreach ($node->lines->items as $line) {
            $segments = [];
            foreach ($line->segments->items as $segment) {
                $segments[] = match ($segment::class) {
                    TemplateLiteralStringSegmentNode::class => $indentation . '    "' . substr(addslashes($segment->value), 0, 64 - strlen($indentation)) . '"',
                    TemplateLiteralExpressionSegmentNode::class => self::printASTNode($segment->expression, $indentation . '    ')
                };
            }

            $lines[] = $indentation . '  Line (indent=' . $line->indentation . ')' . PHP_EOL . join(PHP_EOL, $segments);
        }

        return $indentation . 'TemplateLiteral (indent=' . $node->indentation . ')' . PHP_EOL . join(PHP_EOL, $lines) . PHP_EOL;
    }

    public static function printTagNode(TagNode $node, string $indentation = ''): string
    {
        return $indentation . 'Tag <' . $node->name->value->value . '/>';
    }

    public static function printTernaryOperationNode(TernaryOperationNode $node, string $indentation = ''): string
    {
        $condition = self::printASTNode($node->condition, $indentation . '  ');
        $true = self::printASTNode($node->trueBranch, $indentation . '  ');
        $false = self::printASTNode($node->falseBranch, $indentation . '  ');

        return $indentation . 'TernaryOperation' . PHP_EOL . $condition . PHP_EOL . $true . PHP_EOL . $false;
    }

    public static function printValueReferenceNode(ValueReferenceNode $node, string $indentation = ''): string
    {
        return $indentation . 'ValueReference ' . $node->name->value;
    }
}
