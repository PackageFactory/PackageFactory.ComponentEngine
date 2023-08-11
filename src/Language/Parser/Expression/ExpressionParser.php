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
use PackageFactory\ComponentEngine\Language\Lexer\Token\TokenType;
use PackageFactory\ComponentEngine\Language\Lexer\Token\TokenTypes;
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
    private static TokenTypes $TOKEN_TYPES_ACCESS;
    private static TokenTypes $TOKEN_TYPES_BINARY_OPERATORS;
    private static TokenTypes $TOKEN_TYPES_UNARY;
    private static TokenTypes $TOKEN_TYPES_CLOSING_DELIMITERS;

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
        self::$TOKEN_TYPES_ACCESS ??= TokenTypes::from(
            TokenType::SYMBOL_PERIOD,
            TokenType::SYMBOL_OPTCHAIN
        );
        self::$TOKEN_TYPES_BINARY_OPERATORS ??= TokenTypes::from(
            TokenType::SYMBOL_NULLISH_COALESCE,
            TokenType::SYMBOL_BOOLEAN_AND,
            TokenType::SYMBOL_BOOLEAN_OR,
            TokenType::SYMBOL_STRICT_EQUALS,
            TokenType::SYMBOL_NOT_EQUALS,
            TokenType::SYMBOL_GREATER_THAN,
            TokenType::SYMBOL_LESS_THAN
        );
        self::$TOKEN_TYPES_UNARY ??= TokenTypes::from(
            TokenType::SYMBOL_EXCLAMATIONMARK,
            TokenType::KEYWORD_TRUE,
            TokenType::KEYWORD_FALSE,
            TokenType::KEYWORD_NULL,
            TokenType::KEYWORD_MATCH,
            TokenType::STRING_LITERAL_DELIMITER,
            TokenType::INTEGER_HEXADECIMAL,
            TokenType::INTEGER_DECIMAL,
            TokenType::INTEGER_OCTAL,
            TokenType::INTEGER_BINARY,
            TokenType::WORD,
            TokenType::BRACKET_ANGLE_OPEN,
            TokenType::BRACKET_ROUND_OPEN
        );
        self::$TOKEN_TYPES_CLOSING_DELIMITERS = TokenTypes::from(
            TokenType::BRACKET_CURLY_OPEN,
            TokenType::BRACKET_CURLY_CLOSE,
            TokenType::BRACKET_ROUND_CLOSE,
            TokenType::SYMBOL_COLON,
            TokenType::SYMBOL_COMMA,
            TokenType::SYMBOL_ARROW_SINGLE
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

            if ($lexer->peek(TokenType::SYMBOL_QUESTIONMARK)) {
                if ($this->precedence->mustStopAt(TokenType::SYMBOL_QUESTIONMARK)) {
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
        if ($lexer->peek(TokenType::TEMPLATE_LITERAL_DELIMITER)) {
            $result = $this->parseTemplateLiteral($lexer);
        } else {
            $result = match ($lexer->expectOneOf(self::$TOKEN_TYPES_UNARY)) {
                TokenType::SYMBOL_EXCLAMATIONMARK =>
                    $this->parseUnaryOperation($lexer),
                TokenType::KEYWORD_TRUE,
                TokenType::KEYWORD_FALSE =>
                    $this->parseBooleanLiteral($lexer),
                TokenType::KEYWORD_NULL =>
                    $this->parseNullLiteral($lexer),
                TokenType::STRING_LITERAL_DELIMITER =>
                    $this->parseStringLiteral($lexer),
                TokenType::INTEGER_HEXADECIMAL,
                TokenType::INTEGER_DECIMAL,
                TokenType::INTEGER_OCTAL,
                TokenType::INTEGER_BINARY =>
                    $this->parseIntegerLiteral($lexer),
                TokenType::WORD =>
                    $this->parseValueReference($lexer),
                TokenType::BRACKET_ANGLE_OPEN =>
                    $this->parseTag($lexer),
                TokenType::KEYWORD_MATCH =>
                    $this->parseMatch($lexer),
                TokenType::BRACKET_ROUND_OPEN =>
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
        $lexer->read(TokenType::SYMBOL_EXCLAMATIONMARK);

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
        $lexer->read(TokenType::BRACKET_ROUND_OPEN);
        $start = $lexer->getStartPosition();
        $lexer->skipSpaceAndComments();

        $innerExpressionNode = $this->parse($lexer);

        $lexer->read(TokenType::BRACKET_ROUND_CLOSE);
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

            $lexer->read(TokenType::WORD);
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
        return match ($lexer->getTokenTypeUnderCursor()) {
            TokenType::SYMBOL_PERIOD => AccessType::MANDATORY,
            TokenType::SYMBOL_OPTCHAIN => AccessType::OPTIONAL,
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
        if ($lexer->probe(TokenType::SYMBOL_GREATER_THAN_OR_EQUAL)) {
            $lexer->skipSpaceAndComments();
            return BinaryOperator::GREATER_THAN_OR_EQUAL;
        }

        if ($lexer->probe(TokenType::SYMBOL_LESS_THAN_OR_EQUAL)) {
            $lexer->skipSpaceAndComments();
            return BinaryOperator::LESS_THAN_OR_EQUAL;
        }

        $lexer->readOneOf(self::$TOKEN_TYPES_BINARY_OPERATORS);
        $operator = match ($lexer->getTokenTypeUnderCursor()) {
            TokenType::SYMBOL_NULLISH_COALESCE => BinaryOperator::NULLISH_COALESCE,
            TokenType::SYMBOL_BOOLEAN_AND => BinaryOperator::AND,
            TokenType::SYMBOL_BOOLEAN_OR => BinaryOperator::OR,
            TokenType::SYMBOL_STRICT_EQUALS => BinaryOperator::EQUAL,
            TokenType::SYMBOL_NOT_EQUALS => BinaryOperator::NOT_EQUAL,
            TokenType::SYMBOL_GREATER_THAN => BinaryOperator::GREATER_THAN,
            TokenType::SYMBOL_LESS_THAN => BinaryOperator::LESS_THAN,
            default => throw new LogicException()
        };

        $lexer->skipSpaceAndComments();

        return $operator;
    }

    private function parseTernaryOperation(Lexer $lexer, ExpressionNode $condition): ExpressionNode
    {
        $lexer->read(TokenType::SYMBOL_QUESTIONMARK);
        $lexer->skipSpaceAndComments();

        $trueBranch = $this->parse($lexer);

        $lexer->read(TokenType::SYMBOL_COLON);
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
