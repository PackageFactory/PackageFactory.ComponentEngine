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
use PackageFactory\ComponentEngine\Language\Lexer\Rule\Rule;
use PackageFactory\ComponentEngine\Language\Lexer\Rule\Rules;

final class DebugHelper
{
    public static function describeRule(Rule $tokenType): string
    {
        return $tokenType->value . match ($tokenType) {
            Rule::COMMENT => ' (e.g. "# ...")',

            Rule::KEYWORD_FROM => ' ("from")',
            Rule::KEYWORD_IMPORT => ' ("import")',
            Rule::KEYWORD_EXPORT => ' ("export")',
            Rule::KEYWORD_ENUM => ' ("enum")',
            Rule::KEYWORD_STRUCT => ' ("struct")',
            Rule::KEYWORD_COMPONENT => ' ("component")',
            Rule::KEYWORD_MATCH => ' ("match")',
            Rule::KEYWORD_DEFAULT => ' ("default")',
            Rule::KEYWORD_RETURN => ' ("return")',
            Rule::KEYWORD_TRUE => ' ("true")',
            Rule::KEYWORD_FALSE => ' ("false")',
            Rule::KEYWORD_NULL => ' ("null")',

            Rule::STRING_LITERAL_DELIMITER => ' (""")',
            Rule::STRING_LITERAL_CONTENT => '',

            Rule::INTEGER_BINARY => ' (e.g. "0b1001")',
            Rule::INTEGER_OCTAL => ' (e.g. "0o644")',
            Rule::INTEGER_DECIMAL => ' (e.g. "42")',
            Rule::INTEGER_HEXADECIMAL => ' (e.g. "0xABC")',

            Rule::TEMPLATE_LITERAL_DELIMITER => ' (""""")',
            Rule::TEMPLATE_LITERAL_CONTENT => '',

            Rule::ESCAPE_SEQUENCE_SINGLE_CHARACTER => ' (e.g. "\\\\" or "\\n")',
            Rule::ESCAPE_SEQUENCE_HEXADECIMAL => ' (e.g. "\\xA9")',
            Rule::ESCAPE_SEQUENCE_UNICODE => ' (e.g. "\\u00A9")',
            Rule::ESCAPE_SEQUENCE_UNICODE_CODEPOINT => ' (e.g. "\\u{2F804}")',

            Rule::BRACKET_CURLY_OPEN => ' ("{")',
            Rule::BRACKET_CURLY_CLOSE => ' ("}")',
            Rule::BRACKET_ROUND_OPEN => ' ("(")',
            Rule::BRACKET_ROUND_CLOSE => ' (")")',
            Rule::BRACKET_SQUARE_OPEN => ' ("[")',
            Rule::BRACKET_SQUARE_CLOSE => ' ("]")',
            Rule::BRACKET_ANGLE_OPEN => ' ("<")',
            Rule::BRACKET_ANGLE_CLOSE => ' (">")',

            Rule::SYMBOL_PERIOD => ' (".")',
            Rule::SYMBOL_COLON => ' (":")',
            Rule::SYMBOL_QUESTIONMARK => ' ("?")',
            Rule::SYMBOL_EXCLAMATIONMARK => ' ("!")',
            Rule::SYMBOL_COMMA => ' (",")',
            Rule::SYMBOL_DASH => ' ("-")',
            Rule::SYMBOL_EQUALS => ' ("=")',
            Rule::SYMBOL_SLASH_FORWARD => ' ("/")',
            Rule::SYMBOL_PIPE => ' ("|")',
            Rule::SYMBOL_BOOLEAN_AND => ' ("&&")',
            Rule::SYMBOL_BOOLEAN_OR => ' ("||")',
            Rule::SYMBOL_STRICT_EQUALS => ' ("===")',
            Rule::SYMBOL_NOT_EQUALS => ' ("!==")',
            Rule::SYMBOL_GREATER_THAN => ' (">")',
            Rule::SYMBOL_GREATER_THAN_OR_EQUAL => ' (">=")',
            Rule::SYMBOL_LESS_THAN => ' ("<")',
            Rule::SYMBOL_LESS_THAN_OR_EQUAL => ' ("<=")',
            Rule::SYMBOL_ARROW_SINGLE => ' ("->")',
            Rule::SYMBOL_OPTCHAIN => ' ("?.")',
            Rule::SYMBOL_NULLISH_COALESCE => ' ("??")',
            Rule::SYMBOL_CLOSE_TAG => ' ("</")',

            Rule::WORD => '',
            Rule::TEXT => '',

            Rule::SPACE => '',
            Rule::END_OF_LINE => ''
        };
    }

    public static function describeRules(Rules $tokenTypes): string
    {
        if (count($tokenTypes->items) === 1) {
            return self::describeRule($tokenTypes->items[0]);
        }

        $leadingItems = array_slice($tokenTypes->items, 0, -1);
        $trailingItem = array_slice($tokenTypes->items, -1)[0];

        return join(', ', array_map(
            static fn (Rule $tokenType) => self::describeRule($tokenType),
            $leadingItems
        )) . ' or ' . self::describeRule($trailingItem);
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
