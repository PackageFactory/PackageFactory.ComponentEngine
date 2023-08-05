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
use PackageFactory\ComponentEngine\Language\Parser\BooleanLiteral\BooleanLiteralParser;
use PackageFactory\ComponentEngine\Language\Parser\IntegerLiteral\IntegerLiteralParser;
use PackageFactory\ComponentEngine\Language\Parser\Match\MatchParser;
use PackageFactory\ComponentEngine\Language\Parser\NullLiteral\NullLiteralParser;
use PackageFactory\ComponentEngine\Language\Parser\StringLiteral\StringLiteralParser;
use PackageFactory\ComponentEngine\Language\Parser\Tag\TagParser;
use PackageFactory\ComponentEngine\Language\Parser\TemplateLiteral\TemplateLiteralParser;
use PackageFactory\ComponentEngine\Language\Parser\ValueReference\ValueReferenceParser;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenTypes;

final class ExpressionParser
{
    private readonly BooleanLiteralParser $booleanLiteralParser;
    private readonly NullLiteralParser $nullLiteralParser;
    private readonly StringLiteralParser $stringLiteralParser;
    private readonly IntegerLiteralParser $integerLiteralParser;
    private readonly ValueReferenceParser $valueReferenceParser;
    private readonly TemplateLiteralParser $templateLiteralParser;
    private readonly TagParser $tagParser;
    private readonly MatchParser $matchParser;

    public function __construct(
        private ?TokenType $stopAt = null,
        private Precedence $precedence = Precedence::SEQUENCE
    ) {
        $this->booleanLiteralParser = new BooleanLiteralParser();
        $this->nullLiteralParser = new NullLiteralParser();
        $this->stringLiteralParser = new StringLiteralParser();
        $this->integerLiteralParser = new IntegerLiteralParser();
        $this->valueReferenceParser = new ValueReferenceParser();
        $this->templateLiteralParser = new TemplateLiteralParser();
        $this->tagParser = new TagParser();
        $this->matchParser = new MatchParser();
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ExpressionNode
     */
    public function parse(\Iterator &$tokens): ExpressionNode
    {
        $result = $this->parseUnaryStatement($tokens);

        if ($this->shouldStop($tokens)) {
            return $result;
        }

        $result = match (Scanner::type($tokens)) {
            TokenType::OPERATOR_BOOLEAN_AND,
            TokenType::OPERATOR_BOOLEAN_OR,
            TokenType::COMPARATOR_EQUAL,
            TokenType::COMPARATOR_NOT_EQUAL,
            TokenType::COMPARATOR_GREATER_THAN,
            TokenType::COMPARATOR_GREATER_THAN_OR_EQUAL,
            TokenType::COMPARATOR_LESS_THAN,
            TokenType::COMPARATOR_LESS_THAN_OR_EQUAL => $this->parseBinaryOperation($tokens, $result),
            default => $result
        };

        if ($this->shouldStop($tokens)) {
            return $result;
        }

        $result = match (Scanner::type($tokens)) {
            TokenType::QUESTIONMARK =>
                $this->parseTernaryOperation($tokens, $result),
            default =>
                throw ExpressionCouldNotBeParsed::becauseOfUnexpectedToken(
                    expectedTokenTypes: TokenTypes::from(TokenType::QUESTIONMARK),
                    actualToken: $tokens->current()
                )
        };

        return $result;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ExpressionNode
     */
    private function parseUnaryStatement(\Iterator &$tokens): ExpressionNode
    {
        $result = match (Scanner::type($tokens)) {
            TokenType::OPERATOR_BOOLEAN_NOT =>
                $this->parseUnaryOperation($tokens),
            TokenType::KEYWORD_TRUE,
            TokenType::KEYWORD_FALSE =>
                $this->parseBooleanLiteral($tokens),
            TokenType::KEYWORD_NULL =>
                $this->parseNullLiteral($tokens),
            TokenType::STRING_QUOTED =>
                $this->parseStringLiteral($tokens),
            TokenType::NUMBER_BINARY,
            TokenType::NUMBER_OCTAL,
            TokenType::NUMBER_DECIMAL,
            TokenType::NUMBER_HEXADECIMAL =>
                $this->parseIntegerLiteral($tokens),
            TokenType::STRING =>
                $this->parseValueReference($tokens),
            TokenType::TAG_START_OPENING =>
                $this->parseTag($tokens),
            TokenType::TEMPLATE_LITERAL_START =>
                $this->parseTemplateLiteral($tokens),
            TokenType::KEYWORD_MATCH =>
                $this->parseMatch($tokens),
            TokenType::BRACKET_ROUND_OPEN =>
                $this->parseBracketedExpression($tokens),
            default =>
                throw ExpressionCouldNotBeParsed::becauseOfUnexpectedToken(
                    expectedTokenTypes: TokenTypes::from(
                        TokenType::KEYWORD_TRUE,
                        TokenType::KEYWORD_FALSE,
                        TokenType::KEYWORD_NULL,
                        TokenType::STRING_QUOTED,
                        TokenType::NUMBER_BINARY,
                        TokenType::NUMBER_OCTAL,
                        TokenType::NUMBER_DECIMAL,
                        TokenType::NUMBER_HEXADECIMAL,
                        TokenType::STRING,
                        TokenType::TAG_START_OPENING,
                        TokenType::TEMPLATE_LITERAL_START,
                        TokenType::KEYWORD_MATCH,
                        TokenType::BRACKET_ROUND_OPEN
                    ),
                    actualToken: $tokens->current()
                )
        };

        if (!Scanner::isEnd($tokens)) {
            $result = match (Scanner::type($tokens)) {
                TokenType::PERIOD,
                TokenType::OPTCHAIN => $this->parseAcccess($tokens, $result),
                default => $result
            };
        }

        return $result;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ExpressionNode
     */
    private function parseUnaryOperation(\Iterator &$tokens): ExpressionNode
    {
        $startingToken = $tokens->current();

        $operator = $this->parseUnaryOperator($tokens);
        $operand = $this->parseUnaryStatement($tokens);

        $unaryOperationNode = new UnaryOperationNode(
            rangeInSource: Range::from(
                $startingToken->boundaries->start,
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

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return UnaryOperator
     */
    private function parseUnaryOperator(\Iterator &$tokens): UnaryOperator
    {
        $unaryOperator = match (Scanner::type($tokens)) {
            TokenType::OPERATOR_BOOLEAN_NOT => UnaryOperator::NOT,
            default => throw ExpressionCouldNotBeParsed::becauseOfUnexpectedToken(
                expectedTokenTypes: TokenTypes::from(TokenType::OPERATOR_BOOLEAN_NOT),
                actualToken: $tokens->current()
            )
        };

        Scanner::skipOne($tokens);

        return $unaryOperator;
    }

    private function withStopAt(TokenType $stopAt): self
    {
        $newExpressionParser = clone $this;
        $newExpressionParser->stopAt = $stopAt;

        return $newExpressionParser;
    }

    private function withPrecedence(Precedence $precedence): self
    {
        $newExpressionParser = clone $this;
        $newExpressionParser->precedence = $precedence;

        return $newExpressionParser;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return boolean
     */
    private function shouldStop(\Iterator &$tokens): bool
    {
        Scanner::skipSpaceAndComments($tokens);

        if (Scanner::isEnd($tokens)) {
            return true;
        }

        $type = Scanner::type($tokens);

        if ($this->precedence->mustStopAt($type)) {
            return true;
        }

        if ($this->stopAt && $type === $this->stopAt) {
            return true;
        }

        return false;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ExpressionNode
     */
    private function parseBooleanLiteral(\Iterator &$tokens): ExpressionNode
    {
        $booleanLiteralNode = $this->booleanLiteralParser->parse($tokens);

        return new ExpressionNode(
            rangeInSource: $booleanLiteralNode->rangeInSource,
            root: $booleanLiteralNode
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ExpressionNode
     */
    private function parseNullLiteral(\Iterator &$tokens): ExpressionNode
    {
        $nullLiteralNode = $this->nullLiteralParser->parse($tokens);

        return new ExpressionNode(
            rangeInSource: $nullLiteralNode->rangeInSource,
            root: $nullLiteralNode
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ExpressionNode
     */
    private function parseStringLiteral(\Iterator &$tokens): ExpressionNode
    {
        $stringLiteralNode = $this->stringLiteralParser->parse($tokens);

        return new ExpressionNode(
            rangeInSource: $stringLiteralNode->rangeInSource,
            root: $stringLiteralNode
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ExpressionNode
     */
    private function parseIntegerLiteral(\Iterator &$tokens): ExpressionNode
    {
        $integerLiteralNode = $this->integerLiteralParser->parse($tokens);

        return new ExpressionNode(
            rangeInSource: $integerLiteralNode->rangeInSource,
            root: $integerLiteralNode
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ExpressionNode
     */
    private function parseValueReference(\Iterator &$tokens): ExpressionNode
    {
        $valueReferenceNode = $this->valueReferenceParser->parse($tokens);

        return new ExpressionNode(
            rangeInSource: $valueReferenceNode->rangeInSource,
            root: $valueReferenceNode
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ExpressionNode
     */
    private function parseTag(\Iterator &$tokens): ExpressionNode
    {
        $tagNode = $this->tagParser->parse($tokens);

        return new ExpressionNode(
            rangeInSource: $tagNode->rangeInSource,
            root: $tagNode
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ExpressionNode
     */
    private function parseTemplateLiteral(\Iterator &$tokens): ExpressionNode
    {
        $templateLiteralNode = $this->templateLiteralParser->parse($tokens);

        return new ExpressionNode(
            rangeInSource: $templateLiteralNode->rangeInSource,
            root: $templateLiteralNode
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ExpressionNode
     */
    private function parseMatch(\Iterator &$tokens): ExpressionNode
    {
        $matchNode = $this->matchParser->parse($tokens);

        return new ExpressionNode(
            rangeInSource: $matchNode->rangeInSource,
            root: $matchNode
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ExpressionNode
     */
    private function parseBracketedExpression(\Iterator &$tokens): ExpressionNode
    {
        Scanner::assertType($tokens, TokenType::BRACKET_ROUND_OPEN);

        $openingBracketToken = $tokens->current();

        Scanner::skipOne($tokens);
        Scanner::skipSpaceAndComments($tokens);

        $innerExpressionNode = $this->withStopAt(TokenType::BRACKET_ROUND_CLOSE)->parse($tokens);

        Scanner::assertType($tokens, TokenType::BRACKET_ROUND_CLOSE);

        $closingBracketToken = $tokens->current();

        Scanner::skipOne($tokens);
        Scanner::skipSpaceAndComments($tokens);

        return new ExpressionNode(
            rangeInSource: Range::from(
                $openingBracketToken->boundaries->start,
                $closingBracketToken->boundaries->end
            ),
            root: $innerExpressionNode->root
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @param ExpressionNode $parent
     * @return ExpressionNode
     */
    private function parseAcccess(\Iterator &$tokens, ExpressionNode $parent): ExpressionNode
    {
        $accessTokenTypes = TokenTypes::from(TokenType::PERIOD, TokenType::OPTCHAIN);

        while (!Scanner::isEnd($tokens) && $accessTokenTypes->contains(Scanner::type($tokens))) {
            $type = $this->parseAccessType($tokens);

            Scanner::assertType($tokens, TokenType::STRING);
            $keyToken = $tokens->current();
            Scanner::skipOne($tokens);

            $rangeInSource = Range::from(
                $parent->rangeInSource->start,
                $keyToken->boundaries->end
            );

            $parent = new ExpressionNode(
                rangeInSource: $rangeInSource,
                root: new AccessNode(
                    rangeInSource: $rangeInSource,
                    parent: $parent,
                    type: $type,
                    key: new AccessKeyNode(
                        rangeInSource: $keyToken->boundaries,
                        value: PropertyName::from($keyToken->value)
                    )
                )
            );
        }

        return $parent;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return AccessType
     */
    private function parseAccessType(\Iterator &$tokens): AccessType
    {
        $accessType = match (Scanner::type($tokens)) {
            TokenType::PERIOD => AccessType::MANDATORY,
            TokenType::OPTCHAIN => AccessType::OPTIONAL,
            default => throw ExpressionCouldNotBeParsed::becauseOfUnexpectedToken(
                expectedTokenTypes: TokenTypes::from(TokenType::PERIOD, TokenType::OPTCHAIN),
                actualToken: $tokens->current()
            )
        };

        Scanner::skipOne($tokens);

        return $accessType;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @param ExpressionNode $leftOperand
     * @return ExpressionNode
     */
    private function parseBinaryOperation(\Iterator &$tokens, ExpressionNode $leftOperand): ExpressionNode
    {
        $operator = $this->parseBinaryOperator($tokens);
        $rightOperand = $this
            ->withPrecedence(Precedence::forBinaryOperator($operator))
            ->parse($tokens);
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

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return BinaryOperator
     */
    private function parseBinaryOperator(\Iterator &$tokens): BinaryOperator
    {
        $operator = match (Scanner::type($tokens)) {
            TokenType::OPERATOR_BOOLEAN_AND => BinaryOperator::AND,
            TokenType::OPERATOR_BOOLEAN_OR => BinaryOperator::OR,
            TokenType::COMPARATOR_EQUAL => BinaryOperator::EQUAL,
            TokenType::COMPARATOR_NOT_EQUAL => BinaryOperator::NOT_EQUAL,
            TokenType::COMPARATOR_GREATER_THAN => BinaryOperator::GREATER_THAN,
            TokenType::COMPARATOR_GREATER_THAN_OR_EQUAL => BinaryOperator::GREATER_THAN_OR_EQUAL,
            TokenType::COMPARATOR_LESS_THAN => BinaryOperator::LESS_THAN,
            TokenType::COMPARATOR_LESS_THAN_OR_EQUAL => BinaryOperator::LESS_THAN_OR_EQUAL,
            default => throw ExpressionCouldNotBeParsed::becauseOfUnexpectedToken(
                expectedTokenTypes: TokenTypes::from(
                    TokenType::OPERATOR_BOOLEAN_AND,
                    TokenType::OPERATOR_BOOLEAN_OR,
                    TokenType::COMPARATOR_EQUAL,
                    TokenType::COMPARATOR_NOT_EQUAL,
                    TokenType::COMPARATOR_GREATER_THAN,
                    TokenType::COMPARATOR_GREATER_THAN_OR_EQUAL,
                    TokenType::COMPARATOR_LESS_THAN,
                    TokenType::COMPARATOR_LESS_THAN_OR_EQUAL
                ),
                actualToken: $tokens->current()
            )
        };

        Scanner::skipOne($tokens);
        Scanner::skipSpaceAndComments($tokens);

        return $operator;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @param ExpressionNode $condition
     * @return ExpressionNode
     */
    private function parseTernaryOperation(\Iterator &$tokens, ExpressionNode $condition): ExpressionNode
    {
        Scanner::assertType($tokens, TokenType::QUESTIONMARK);
        Scanner::skipOne($tokens);
        Scanner::skipSpaceAndComments($tokens);

        $trueBranch = $this->withStopAt(TokenType::COLON)->parse($tokens);

        Scanner::assertType($tokens, TokenType::COLON);
        Scanner::skipOne($tokens);
        Scanner::skipSpaceAndComments($tokens);

        $falseBranch = $this->parse($tokens);

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
