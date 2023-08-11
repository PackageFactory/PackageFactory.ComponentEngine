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

use LogicException;
use PackageFactory\ComponentEngine\Domain\AttributeName\AttributeName;
use PackageFactory\ComponentEngine\Domain\TagName\TagName;
use PackageFactory\ComponentEngine\Framework\PHP\Singleton\Singleton;
use PackageFactory\ComponentEngine\Language\AST\Node\Expression\ExpressionNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StringLiteral\StringLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\AttributeNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\AttributeNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\AttributeNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\ChildNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\TagNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\TagNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Text\TextNode;
use PackageFactory\ComponentEngine\Language\Lexer\Lexer;
use PackageFactory\ComponentEngine\Language\Lexer\Rule\Rule;
use PackageFactory\ComponentEngine\Language\Lexer\Rule\Rules;
use PackageFactory\ComponentEngine\Language\Parser\Expression\ExpressionParser;
use PackageFactory\ComponentEngine\Language\Parser\StringLiteral\StringLiteralParser;
use PackageFactory\ComponentEngine\Language\Parser\Text\TextParser;
use PackageFactory\ComponentEngine\Parser\Source\Range;

final class TagParser
{
    use Singleton;

    private static Rules $TOKEN_TYPES_ATTRIBUTE_DELIMITERS;

    private ?StringLiteralParser $stringLiteralParser = null;
    private ?TextParser $textParser = null;
    private ?ExpressionParser $expressionParser = null;

    private function __construct()
    {
        self::$TOKEN_TYPES_ATTRIBUTE_DELIMITERS ??= Rules::from(
            Rule::STRING_LITERAL_DELIMITER,
            Rule::BRACKET_CURLY_OPEN
        );
    }

    public function parse(Lexer $lexer): TagNode
    {
        $lexer->read(Rule::BRACKET_ANGLE_OPEN);
        $start = $lexer->getStartPosition();

        $name = $this->parseName($lexer);
        $attributes = $this->parseAttributes($lexer);

        if ($lexer->probe(Rule::SYMBOL_SLASH_FORWARD)) {
            $lexer->read(Rule::BRACKET_ANGLE_CLOSE);
            $end = $lexer->getEndPosition();

            return new TagNode(
                rangeInSource: Range::from($start, $end),
                name: $name,
                attributes: $attributes,
                children: new ChildNodes(),
                isSelfClosing: true
            );
        }

        $lexer->read(Rule::BRACKET_ANGLE_CLOSE);
        $children = $this->parseChildren($lexer);

        $this->readClosingTagName($lexer, $name->value);
        $end = $lexer->getEndPosition();

        return new TagNode(
            rangeInSource: Range::from($start, $end),
            name: $name,
            attributes: $attributes,
            children: $children,
            isSelfClosing: false
        );
    }

    private function parseName(Lexer $lexer): TagNameNode
    {
        $lexer->read(Rule::WORD);
        $tagNameNode = new TagNameNode(
            rangeInSource: Range::from(
                $lexer->getStartPosition(),
                $lexer->getEndPosition()
            ),
            value: TagName::from($lexer->getBuffer())
        );

        $lexer->skipSpace();

        return $tagNameNode;
    }

    private function parseAttributes(Lexer $lexer): AttributeNodes
    {
        $items = [];
        while ($lexer->peek(Rule::WORD)) {
            $items[] = $this->parseAttribute($lexer);
            $lexer->skipSpace();
        }

        return new AttributeNodes(...$items);
    }

    private function parseAttribute(Lexer $lexer): AttributeNode
    {
        $attributeNameNode = $this->parseAttributeName($lexer);
        $attributeValueNode = $this->parseAttributeValue($lexer);

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

    private function parseAttributeName(Lexer $lexer): AttributeNameNode
    {
        $lexer->read(Rule::WORD);

        return new AttributeNameNode(
            rangeInSource: $lexer->getCursorRange(),
            value: AttributeName::from($lexer->getBuffer())
        );
    }

    private function parseAttributeValue(Lexer $lexer): null|StringLiteralNode|ExpressionNode
    {
        if ($lexer->probe(Rule::SYMBOL_EQUALS)) {
            return match ($lexer->expectOneOf(self::$TOKEN_TYPES_ATTRIBUTE_DELIMITERS)) {
                Rule::STRING_LITERAL_DELIMITER =>
                    $this->parseString($lexer),
                Rule::BRACKET_CURLY_OPEN =>
                    $this->parseExpression($lexer),
                default => throw new LogicException()
            };
        }

        return null;
    }

    private function parseString(Lexer $lexer): StringLiteralNode
    {
        $this->stringLiteralParser ??= StringLiteralParser::singleton();
        return $this->stringLiteralParser->parse($lexer);
    }

    private function parseExpression(Lexer $lexer): ExpressionNode
    {
        $this->expressionParser ??= new ExpressionParser();

        $lexer->read(Rule::BRACKET_CURLY_OPEN);

        $expressionNode =  $this->expressionParser->parse($lexer);

        $lexer->read(Rule::BRACKET_CURLY_CLOSE);

        return $expressionNode;
    }

    private function parseChildren(Lexer $lexer): ChildNodes
    {
        $items = [];
        $preserveLeadingSpace = false;

        while (!$lexer->peek(Rule::SYMBOL_CLOSE_TAG)) {
            if ($lexer->peek(Rule::BRACKET_ANGLE_OPEN)) {
                $items[] = $this->parse($lexer);
                $preserveLeadingSpace = !$lexer->peek(Rule::END_OF_LINE);
                continue;
            }

            if ($lexer->peek(Rule::BRACKET_CURLY_OPEN)) {
                $items[] = $this->parseExpression($lexer);
                $preserveLeadingSpace = !$lexer->peek(Rule::END_OF_LINE);
                continue;
            }

            if ($textNode = $this->parseText($lexer, $preserveLeadingSpace)) {
                $items[] = $textNode;
            }
        }

        return new ChildNodes(...$items);
    }

    private function parseText(Lexer $lexer, bool $preserveLeadingSpace): ?TextNode
    {
        $this->textParser ??= TextParser::singleton();
        return $this->textParser->parse($lexer, $preserveLeadingSpace);
    }

    private function readClosingTagName(Lexer $lexer, TagName $expectedName): void
    {
        $lexer->read(Rule::SYMBOL_CLOSE_TAG);
        $start = $lexer->getStartPosition();

        $lexer->read(Rule::WORD);
        $closingName = $lexer->getBuffer();

        $lexer->read(Rule::BRACKET_ANGLE_CLOSE);
        $end = $lexer->getEndPosition();

        if ($closingName !== $expectedName->value) {
            throw TagCouldNotBeParsed::becauseOfClosingTagNameMismatch(
                expectedTagName: $expectedName,
                actualTagName: $closingName,
                affectedRangeInSource: Range::from($start, $end)
            );
        }
    }
}
