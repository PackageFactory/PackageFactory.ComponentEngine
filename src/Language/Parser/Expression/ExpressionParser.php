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

namespace PackageFactory\ComponentEngine\Language\Parser\Expression;

use LogicException;
use PackageFactory\ComponentEngine\Domain\PropertyName\PropertyName;
use PackageFactory\ComponentEngine\Language\AST\Node\Access\AccessKeyNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Access\AccessNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Access\AccessType;
use PackageFactory\ComponentEngine\Language\AST\Node\BinaryOperation\BinaryOperationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\BinaryOperation\BinaryOperator;
use PackageFactory\ComponentEngine\Language\AST\Node\Expression\ExpressionNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TernaryOperation\TernaryOperationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\UnaryOperation\UnaryOperationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\UnaryOperation\UnaryOperator;
use PackageFactory\ComponentEngine\Language\Lexer\Lexer;
use PackageFactory\ComponentEngine\Language\Lexer\Rule\Rule;
use PackageFactory\ComponentEngine\Language\Lexer\Rule\Rules;
use PackageFactory\ComponentEngine\Language\Parser\BooleanLiteral\BooleanLiteralParser;
use PackageFactory\ComponentEngine\Language\Parser\IntegerLiteral\IntegerLiteralParser;
use PackageFactory\ComponentEngine\Language\Parser\Match\MatchParser;
use PackageFactory\ComponentEngine\Language\Parser\NullLiteral\NullLiteralParser;
use PackageFactory\ComponentEngine\Language\Parser\StringLiteral\StringLiteralParser;
use PackageFactory\ComponentEngine\Language\Parser\Tag\TagParser;
use PackageFactory\ComponentEngine\Language\Parser\TemplateLiteral\TemplateLiteralParser;
use PackageFactory\ComponentEngine\Language\Parser\ValueReference\ValueReferenceParser;
use PackageFactory\ComponentEngine\Parser\Source\Range;

final class ExpressionParser
{
    private static Rules $TOKEN_TYPES_ACCESS;
    private static Rules $TOKEN_TYPES_BINARY_OPERATORS;
    private static Rules $TOKEN_TYPES_UNARY;
    private static Rules $TOKEN_TYPES_CLOSING_DELIMITERS;

    private ?BooleanLiteralParser $booleanLiteralParser = null;
    private ?IntegerLiteralParser $integerLiteralParser = null;
    private ?MatchParser $matchParser = null;
    private ?NullLiteralParser $nullLiteralParser = null;
    private ?StringLiteralParser $stringLiteralParser = null;
    private ?TagParser $tagParser = null;
    private ?TemplateLiteralParser $templateLiteralParser = null;
    private ?ValueReferenceParser $valueReferenceParser = null;

    public function __construct(
        private Precedence $precedence = Precedence::SEQUENCE
    ) {
        self::$TOKEN_TYPES_ACCESS ??= Rules::from(
            Rule::SYMBOL_PERIOD,
            Rule::SYMBOL_OPTCHAIN
        );
        self::$TOKEN_TYPES_BINARY_OPERATORS ??= Rules::from(
            Rule::SYMBOL_NULLISH_COALESCE,
            Rule::SYMBOL_BOOLEAN_AND,
            Rule::SYMBOL_BOOLEAN_OR,
            Rule::SYMBOL_STRICT_EQUALS,
            Rule::SYMBOL_NOT_EQUALS,
            Rule::SYMBOL_GREATER_THAN,
            Rule::SYMBOL_LESS_THAN
        );
        self::$TOKEN_TYPES_UNARY ??= Rules::from(
            Rule::SYMBOL_EXCLAMATIONMARK,
            Rule::KEYWORD_TRUE,
            Rule::KEYWORD_FALSE,
            Rule::KEYWORD_NULL,
            Rule::KEYWORD_MATCH,
            Rule::STRING_LITERAL_DELIMITER,
            Rule::INTEGER_HEXADECIMAL,
            Rule::INTEGER_DECIMAL,
            Rule::INTEGER_OCTAL,
            Rule::INTEGER_BINARY,
            Rule::WORD,
            Rule::BRACKET_ANGLE_OPEN,
            Rule::BRACKET_ROUND_OPEN
        );
        self::$TOKEN_TYPES_CLOSING_DELIMITERS = Rules::from(
            Rule::BRACKET_CURLY_OPEN,
            Rule::BRACKET_CURLY_CLOSE,
            Rule::BRACKET_ROUND_CLOSE,
            Rule::SYMBOL_COLON,
            Rule::SYMBOL_COMMA,
            Rule::SYMBOL_ARROW_SINGLE
        );
    }

    public function parse(Lexer $lexer): ExpressionNode
    {
        $result = $this->parseUnaryStatement($lexer);

        while (!$lexer->isEnd()) {
            $lexer->skipSpaceAndComments();

            if ($lexer->peekOneOf(self::$TOKEN_TYPES_CLOSING_DELIMITERS)) {
                return $result;
            }

            if ($lexer->probeOneOf(self::$TOKEN_TYPES_ACCESS)) {
                $result = $this->parseAcccess($lexer, $result);
                continue;
            }

            if ($lexer->peek(Rule::SYMBOL_QUESTIONMARK)) {
                if ($this->precedence->mustStopAt(Rule::SYMBOL_QUESTIONMARK)) {
                    return $result;
                }

                $result = $this->parseTernaryOperation($lexer, $result);
                continue;
            }

            if ($tokenType = $lexer->peekOneOf(self::$TOKEN_TYPES_BINARY_OPERATORS)) {
                if ($this->precedence->mustStopAt($tokenType)) {
                    return $result;
                }

                $result = $this->parseBinaryOperation($lexer, $result);
                continue;
            }

            return $result;
        }

        return $result;
    }

    private function parseUnaryStatement(Lexer $lexer): ExpressionNode
    {
        if ($lexer->peek(Rule::TEMPLATE_LITERAL_DELIMITER)) {
            $result = $this->parseTemplateLiteral($lexer);
        } else {
            $result = match ($lexer->expectOneOf(self::$TOKEN_TYPES_UNARY)) {
                Rule::SYMBOL_EXCLAMATIONMARK =>
                    $this->parseUnaryOperation($lexer),
                Rule::KEYWORD_TRUE,
                Rule::KEYWORD_FALSE =>
                    $this->parseBooleanLiteral($lexer),
                Rule::KEYWORD_NULL =>
                    $this->parseNullLiteral($lexer),
                Rule::STRING_LITERAL_DELIMITER =>
                    $this->parseStringLiteral($lexer),
                Rule::INTEGER_HEXADECIMAL,
                Rule::INTEGER_DECIMAL,
                Rule::INTEGER_OCTAL,
                Rule::INTEGER_BINARY =>
                    $this->parseIntegerLiteral($lexer),
                Rule::WORD =>
                    $this->parseValueReference($lexer),
                Rule::BRACKET_ANGLE_OPEN =>
                    $this->parseTag($lexer),
                Rule::KEYWORD_MATCH =>
                    $this->parseMatch($lexer),
                Rule::BRACKET_ROUND_OPEN =>
                    $this->parseBracketedExpression($lexer),
                default => throw new LogicException()
            };
        }

        $lexer->skipSpaceAndComments();

        return $result;
    }

    private function parseUnaryOperation(Lexer $lexer): ExpressionNode
    {
        $start = $lexer->getStartPosition();

        $operator = $this->parseUnaryOperator($lexer);
        $operand = $this->parseUnaryStatement($lexer);

        $unaryOperationNode = new UnaryOperationNode(
            rangeInSource: Range::from(
                $start,
                $operand->rangeInSource->end
            ),
            operator: $operator,
            operand: $operand
        );

        return new ExpressionNode(
            rangeInSource: $unaryOperationNode->rangeInSource,
            root: $unaryOperationNode
        );
    }

    private function parseUnaryOperator(Lexer $lexer): UnaryOperator
    {
        $lexer->read(Rule::SYMBOL_EXCLAMATIONMARK);

        $unaryOperator = UnaryOperator::NOT;

        $lexer->skipSpaceAndComments();

        return $unaryOperator;
    }

    private function withPrecedence(Precedence $precedence): self
    {
        return new self(
            precedence: $precedence
        );
    }

    private function parseBooleanLiteral(Lexer $lexer): ExpressionNode
    {
        $this->booleanLiteralParser ??= BooleanLiteralParser::singleton();

        $booleanLiteralNode = $this->booleanLiteralParser->parse($lexer);

        return new ExpressionNode(
            rangeInSource: $booleanLiteralNode->rangeInSource,
            root: $booleanLiteralNode
        );
    }

    private function parseNullLiteral(Lexer $lexer): ExpressionNode
    {
        $this->nullLiteralParser ??= NullLiteralParser::singleton();

        $nullLiteralNode = $this->nullLiteralParser->parse($lexer);

        return new ExpressionNode(
            rangeInSource: $nullLiteralNode->rangeInSource,
            root: $nullLiteralNode
        );
    }

    private function parseStringLiteral(Lexer $lexer): ExpressionNode
    {
        $this->stringLiteralParser ??= StringLiteralParser::singleton();

        $stringLiteralNode = $this->stringLiteralParser->parse($lexer);

        return new ExpressionNode(
            rangeInSource: $stringLiteralNode->rangeInSource,
            root: $stringLiteralNode
        );
    }

    private function parseIntegerLiteral(Lexer $lexer): ExpressionNode
    {
        $this->integerLiteralParser ??= IntegerLiteralParser::singleton();

        $integerLiteralNode = $this->integerLiteralParser->parse($lexer);

        return new ExpressionNode(
            rangeInSource: $integerLiteralNode->rangeInSource,
            root: $integerLiteralNode
        );
    }

    private function parseValueReference(Lexer $lexer): ExpressionNode
    {
        $this->valueReferenceParser ??= ValueReferenceParser::singleton();

        $valueReferenceNode = $this->valueReferenceParser->parse($lexer);

        return new ExpressionNode(
            rangeInSource: $valueReferenceNode->rangeInSource,
            root: $valueReferenceNode
        );
    }

    private function parseTag(Lexer $lexer): ExpressionNode
    {
        $this->tagParser ??= TagParser::singleton();

        $tagNode = $this->tagParser->parse($lexer);

        return new ExpressionNode(
            rangeInSource: $tagNode->rangeInSource,
            root: $tagNode
        );
    }

    private function parseTemplateLiteral(Lexer $lexer): ExpressionNode
    {
        $this->templateLiteralParser ??= TemplateLiteralParser::singleton();

        $templateLiteralNode = $this->templateLiteralParser->parse($lexer);

        return new ExpressionNode(
            rangeInSource: $templateLiteralNode->rangeInSource,
            root: $templateLiteralNode
        );
    }

    private function parseMatch(Lexer $lexer): ExpressionNode
    {
        $this->matchParser ??= MatchParser::singleton();

        $matchNode = $this->matchParser->parse($lexer);

        return new ExpressionNode(
            rangeInSource: $matchNode->rangeInSource,
            root: $matchNode
        );
    }

    private function parseBracketedExpression(Lexer $lexer): ExpressionNode
    {
        $lexer->read(Rule::BRACKET_ROUND_OPEN);
        $start = $lexer->getStartPosition();
        $lexer->skipSpaceAndComments();

        $innerExpressionNode = $this->parse($lexer);

        $lexer->read(Rule::BRACKET_ROUND_CLOSE);
        $end = $lexer->getEndPosition();
        $lexer->skipSpaceAndComments();

        return new ExpressionNode(
            rangeInSource: Range::from($start, $end),
            root: $innerExpressionNode->root
        );
    }

    private function parseAcccess(Lexer $lexer, ExpressionNode $parent): ExpressionNode
    {
        while (!$lexer->isEnd()) {
            $type = $this->parseAccessType($lexer);

            $lexer->read(Rule::WORD);
            $accessNode = new AccessNode(
                rangeInSource: $parent->rangeInSource->start->toRange(
                    $lexer->getEndPosition()
                ),
                parent: $parent,
                type: $type,
                key: new AccessKeyNode(
                    rangeInSource: $lexer->getCursorRange(),
                    value: PropertyName::from($lexer->getBuffer())
                )
            );

            $parent = new ExpressionNode(
                rangeInSource: $accessNode->rangeInSource,
                root: $accessNode
            );

            $lexer->skipSpaceAndComments();

            if (!$lexer->probeOneOf(self::$TOKEN_TYPES_ACCESS)) {
                break;
            }
        }

        return $parent;
    }

    private function parseAccessType(Lexer $lexer): AccessType
    {
        return match ($lexer->getRuleUnderCursor()) {
            Rule::SYMBOL_PERIOD => AccessType::MANDATORY,
            Rule::SYMBOL_OPTCHAIN => AccessType::OPTIONAL,
            default => throw new LogicException()
        };
    }

    private function parseBinaryOperation(Lexer $lexer, ExpressionNode $leftOperand): ExpressionNode
    {
        $operator = $this->parseBinaryOperator($lexer);
        $rightOperand = $this
            ->withPrecedence(Precedence::forBinaryOperator($operator))
            ->parse($lexer);
        $rangeInSource = Range::from(
            $leftOperand->rangeInSource->start,
            $rightOperand->rangeInSource->end
        );

        return new ExpressionNode(
            rangeInSource: $rangeInSource,
            root: new BinaryOperationNode(
                rangeInSource: $rangeInSource,
                leftOperand: $leftOperand,
                operator: $operator,
                rightOperand: $rightOperand
            )
        );
    }

    private function parseBinaryOperator(Lexer $lexer): BinaryOperator
    {
        if ($lexer->probe(Rule::SYMBOL_GREATER_THAN_OR_EQUAL)) {
            $lexer->skipSpaceAndComments();
            return BinaryOperator::GREATER_THAN_OR_EQUAL;
        }

        if ($lexer->probe(Rule::SYMBOL_LESS_THAN_OR_EQUAL)) {
            $lexer->skipSpaceAndComments();
            return BinaryOperator::LESS_THAN_OR_EQUAL;
        }

        $lexer->readOneOf(self::$TOKEN_TYPES_BINARY_OPERATORS);
        $operator = match ($lexer->getRuleUnderCursor()) {
            Rule::SYMBOL_NULLISH_COALESCE => BinaryOperator::NULLISH_COALESCE,
            Rule::SYMBOL_BOOLEAN_AND => BinaryOperator::AND,
            Rule::SYMBOL_BOOLEAN_OR => BinaryOperator::OR,
            Rule::SYMBOL_STRICT_EQUALS => BinaryOperator::EQUAL,
            Rule::SYMBOL_NOT_EQUALS => BinaryOperator::NOT_EQUAL,
            Rule::SYMBOL_GREATER_THAN => BinaryOperator::GREATER_THAN,
            Rule::SYMBOL_LESS_THAN => BinaryOperator::LESS_THAN,
            default => throw new LogicException()
        };

        $lexer->skipSpaceAndComments();

        return $operator;
    }

    private function parseTernaryOperation(Lexer $lexer, ExpressionNode $condition): ExpressionNode
    {
        $lexer->read(Rule::SYMBOL_QUESTIONMARK);
        $lexer->skipSpaceAndComments();

        $trueBranch = $this->parse($lexer);

        $lexer->read(Rule::SYMBOL_COLON);
        $lexer->skipSpaceAndComments();

        $falseBranch = $this->parse($lexer);

        $root = new TernaryOperationNode(
            condition: $condition,
            trueBranch: $trueBranch,
            falseBranch: $falseBranch
        );

        return new ExpressionNode(
            rangeInSource: $root->rangeInSource,
            root: $root
        );
    }
}
