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

namespace PackageFactory\ComponentEngine\Language\Parser\TemplateLiteral;

use PackageFactory\ComponentEngine\Framework\PHP\Singleton\Singleton;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralExpressionSegmentNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralSegments;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralStringSegmentNode;
use PackageFactory\ComponentEngine\Language\Parser\Expression\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class TemplateLiteralParser
{
    use Singleton;

    private ?ExpressionParser $expressionParser = null;

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return TemplateLiteralNode
     */
    public function parse(\Iterator &$tokens): TemplateLiteralNode
    {
        Scanner::assertType($tokens, TokenType::TEMPLATE_LITERAL_START);
        $startingDelimiterToken = $tokens->current();
        Scanner::skipOne($tokens);

        $segments = $this->parseSegments($tokens);

        Scanner::assertType($tokens, TokenType::TEMPLATE_LITERAL_END);
        $finalDelimiterToken = $tokens->current();
        Scanner::skipOne($tokens);

        return new TemplateLiteralNode(
            rangeInSource: Range::from(
                $startingDelimiterToken->boundaries->start,
                $finalDelimiterToken->boundaries->end
            ),
            segments: $segments
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return TemplateLiteralSegments
     */
    public function parseSegments(\Iterator &$tokens): TemplateLiteralSegments
    {
        $items = [];
        while (Scanner::type($tokens) !== TokenType::TEMPLATE_LITERAL_END) {
            $items[] = match (Scanner::type($tokens)) {
                TokenType::STRING_QUOTED => $this->parseStringSegment($tokens),
                TokenType::DOLLAR => $this->parseExpressionSegment($tokens),
                default => throw new \Exception(__METHOD__ . ' for ' . Scanner::type($tokens)->value .  ' is not implemented yet!')
            };
        }

        return new TemplateLiteralSegments(...$items);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return TemplateLiteralStringSegmentNode
     */
    public function parseStringSegment(\Iterator &$tokens): TemplateLiteralStringSegmentNode
    {
        Scanner::assertType($tokens, TokenType::STRING_QUOTED);
        $stringToken = $tokens->current();
        Scanner::skipOne($tokens);

        return new TemplateLiteralStringSegmentNode(
            rangeInSource: $stringToken->boundaries,
            value: $stringToken->value
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return TemplateLiteralExpressionSegmentNode
     */
    public function parseExpressionSegment(\Iterator &$tokens): TemplateLiteralExpressionSegmentNode
    {
        $this->expressionParser ??= new ExpressionParser(
            stopAt: TokenType::BRACKET_CURLY_CLOSE
        );

        Scanner::assertType($tokens, TokenType::DOLLAR);
        $dollarToken = $tokens->current();
        Scanner::skipOne($tokens);

        Scanner::assertType($tokens, TokenType::BRACKET_CURLY_OPEN);
        Scanner::skipOne($tokens);

        $expression = $this->expressionParser->parse($tokens);

        Scanner::assertType($tokens, TokenType::BRACKET_CURLY_CLOSE);
        $closingBracketToken = $tokens->current();
        Scanner::skipOne($tokens);

        return new TemplateLiteralExpressionSegmentNode(
            rangeInSource: Range::from(
                $dollarToken->boundaries->start,
                $closingBracketToken->boundaries->end
            ),
            expression: $expression
        );
    }
}
