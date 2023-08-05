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

namespace PackageFactory\ComponentEngine\Language\Parser\Tag;

use PackageFactory\ComponentEngine\Domain\AttributeName\AttributeName;
use PackageFactory\ComponentEngine\Domain\TagName\TagName;
use PackageFactory\ComponentEngine\Language\AST\Node\Expression\ExpressionNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StringLiteral\StringLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\AttributeNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\AttributeNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\AttributeNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\ChildNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\TagNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\TagNode;
use PackageFactory\ComponentEngine\Language\Parser\Expression\ExpressionParser;
use PackageFactory\ComponentEngine\Language\Parser\StringLiteral\StringLiteralParser;
use PackageFactory\ComponentEngine\Language\Parser\Text\TextParser;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenTypes;

final class TagParser
{
    private readonly StringLiteralParser $stringLiteralParser;
    private readonly TextParser $textParser;
    private ?ExpressionParser $expressionParser;

    public function __construct()
    {
        $this->stringLiteralParser = new StringLiteralParser();
        $this->textParser = new TextParser();
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return TagNode
     */
    public function parse(\Iterator &$tokens): TagNode
    {
        $tagStartOpeningToken = $this->extractTagStartOpeningToken($tokens);
        $tagNameNode = $this->parseTagName($tokens);
        $attributeNodes = $this->parseAttributes($tokens);

        if ($tagSelfCloseToken = $this->extractTagSelfCloseToken($tokens)) {
            return new TagNode(
                rangeInSource: Range::from(
                    $tagStartOpeningToken->boundaries->start,
                    $tagSelfCloseToken->boundaries->end
                ),
                name: $tagNameNode,
                attributes: $attributeNodes,
                children: new ChildNodes(),
                isSelfClosing: true
            );
        } else {
            $this->skipTagEndToken($tokens);
            $children = $this->parseChildren($tokens);
            $this->skipTagStartClosingToken($tokens);
            $this->assertAndSkipClosingTagName($tokens, $tagNameNode);
            $closingTagEndToken = $this->extractTagEndToken($tokens);

            return new TagNode(
                rangeInSource: Range::from(
                    $tagStartOpeningToken->boundaries->start,
                    $closingTagEndToken->boundaries->end
                ),
                name: $tagNameNode,
                attributes: $attributeNodes,
                children: $children,
                isSelfClosing: false
            );
        }
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return Token
     */
    private function extractTagStartOpeningToken(\Iterator &$tokens): Token
    {
        Scanner::assertType($tokens, TokenType::TAG_START_OPENING);
        $tagStartOpeningToken = $tokens->current();
        Scanner::skipOne($tokens);

        return $tagStartOpeningToken;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return TagNameNode
     */
    private function parseTagName(\Iterator &$tokens): TagNameNode
    {
        Scanner::assertType($tokens, TokenType::STRING);
        $tagNameToken = $tokens->current();
        Scanner::skipOne($tokens);

        return new TagNameNode(
            rangeInSource: $tagNameToken->boundaries,
            value: TagName::from($tagNameToken->value)
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return AttributeNodes
     */
    private function parseAttributes(\Iterator &$tokens): AttributeNodes
    {
        $items = [];
        while (!$this->isTagEnd($tokens)) {
            Scanner::skipSpace($tokens);

            $items[] = $this->parseAttribute($tokens);

            Scanner::skipSpace($tokens);
        }

        return new AttributeNodes(...$items);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return boolean
     */
    private function isTagEnd(\Iterator $tokens): bool
    {
        return (
            Scanner::type($tokens) === TokenType::TAG_END ||
            Scanner::type($tokens) === TokenType::TAG_SELF_CLOSE
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return AttributeNode
     */
    private function parseAttribute(\Iterator &$tokens): AttributeNode
    {
        $attributeNameNode = $this->parseAttributeName($tokens);
        $attributeValueNode = $this->parseAttributeValue($tokens);

        return new AttributeNode(
            rangeInSource: Range::from(
                $attributeNameNode->rangeInSource->start,
                $attributeValueNode?->rangeInSource->end ??
                    $attributeNameNode->rangeInSource->end
            ),
            name: $attributeNameNode,
            value: $attributeValueNode
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return AttributeNameNode
     */
    private function parseAttributeName(\Iterator &$tokens): AttributeNameNode
    {
        Scanner::assertType($tokens, TokenType::STRING);
        $attributeNameToken = $tokens->current();
        Scanner::skipOne($tokens);

        return new AttributeNameNode(
            rangeInSource: $attributeNameToken->boundaries,
            value: AttributeName::from($attributeNameToken->value)
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return null|StringLiteralNode|ExpressionNode
     */
    private function parseAttributeValue(\Iterator &$tokens): null|StringLiteralNode|ExpressionNode
    {
        if (Scanner::type($tokens) === TokenType::EQUALS) {
            Scanner::skipOne($tokens);

            return match (Scanner::type($tokens)) {
                TokenType::STRING_QUOTED =>
                    $this->stringLiteralParser->parse($tokens),
                TokenType::BRACKET_CURLY_OPEN =>
                    $this->parseExpression($tokens),
                default => throw TagCouldNotBeParsed::becauseOfUnexpectedToken(
                    expectedTokenTypes: TokenTypes::from(
                        TokenType::STRING_QUOTED,
                        TokenType::BRACKET_CURLY_OPEN
                    ),
                    actualToken: $tokens->current()
                )
            };
        }

        return null;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ExpressionNode
     */
    private function parseExpression(\Iterator &$tokens): ExpressionNode
    {
        $this->expressionParser ??= new ExpressionParser(
            stopAt: TokenType::BRACKET_CURLY_CLOSE
        );

        Scanner::assertType($tokens, TokenType::BRACKET_CURLY_OPEN);
        Scanner::skipOne($tokens);

        $expressionNode =  $this->expressionParser->parse($tokens);

        Scanner::assertType($tokens, TokenType::BRACKET_CURLY_CLOSE);
        Scanner::skipOne($tokens);

        return $expressionNode;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return null|Token
     */
    private function extractTagSelfCloseToken(\Iterator &$tokens): ?Token
    {
        if (Scanner::type($tokens) === TokenType::TAG_SELF_CLOSE) {
            $tagSelfCloseToken = $tokens->current();
            Scanner::skipOne($tokens);

            return $tagSelfCloseToken;
        }

        return null;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return void
     */
    private function skipTagEndToken(\Iterator &$tokens): void
    {
        Scanner::assertType($tokens, TokenType::TAG_END);
        Scanner::skipOne($tokens);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ChildNodes
     */
    private function parseChildren(\Iterator &$tokens): ChildNodes
    {
        $items = [];
        $preserveLeadingSpace = false;
        while (Scanner::type($tokens) !== TokenType::TAG_START_CLOSING) {
            if ($textNode = $this->textParser->parse($tokens, $preserveLeadingSpace)) {
                $items[] = $textNode;
            }

            if (Scanner::type($tokens) === TokenType::TAG_START_OPENING) {
                $items[] = $this->parse($tokens);
                $preserveLeadingSpace = Scanner::type($tokens) !== TokenType::END_OF_LINE;
                continue;
            }

            if (Scanner::type($tokens) === TokenType::BRACKET_CURLY_OPEN) {
                $items[] = $this->parseExpression($tokens);
                $preserveLeadingSpace = Scanner::type($tokens) !== TokenType::END_OF_LINE;
                continue;
            }

            if (Scanner::type($tokens) !== TokenType::TAG_START_CLOSING) {
                throw TagCouldNotBeParsed::becauseOfUnexpectedToken(
                    expectedTokenTypes: TokenTypes::from(
                        TokenType::TAG_START_OPENING,
                        TokenType::TAG_START_CLOSING,
                        TokenType::BRACKET_CURLY_OPEN
                    ),
                    actualToken: $tokens->current()
                );
            }
        }

        return new ChildNodes(...$items);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return void
     */
    private function skipTagStartClosingToken(\Iterator &$tokens): void
    {
        Scanner::assertType($tokens, TokenType::TAG_START_CLOSING);
        Scanner::skipOne($tokens);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @param TagNameNode $openingTagNameNode
     * @return void
     */
    private function assertAndSkipClosingTagName(\Iterator &$tokens, TagNameNode $openingTagNameNode): void
    {
        Scanner::assertType($tokens, TokenType::STRING);
        $tagNameToken = $tokens->current();
        Scanner::skipOne($tokens);

        if ($tagNameToken->value !== $openingTagNameNode->value->value) {
            throw TagCouldNotBeParsed::becauseOfClosingTagNameMismatch(
                expectedTagName: $openingTagNameNode->value,
                actualTagName: $tagNameToken->value,
                affectedRangeInSource: $tagNameToken->boundaries
            );
        }
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return Token
     */
    private function extractTagEndToken(\Iterator &$tokens): Token
    {
        Scanner::assertType($tokens, TokenType::TAG_END);
        $tagEndToken = $tokens->current();
        Scanner::skipOne($tokens);

        return $tagEndToken;
    }
}
