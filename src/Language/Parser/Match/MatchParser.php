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

namespace PackageFactory\ComponentEngine\Language\Parser\Match;

use PackageFactory\ComponentEngine\Language\AST\Node\Expression\ExpressionNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Expression\ExpressionNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Match\InvalidMatchArmNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Match\MatchArmNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Match\MatchArmNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Match\MatchNode;
use PackageFactory\ComponentEngine\Language\Parser\Expression\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class MatchParser
{
    private ?ExpressionParser $subjectParser = null;
    private ?ExpressionParser $matchArmLeftParser = null;
    private ?ExpressionParser $matchArmRightParser = null;

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return MatchNode
     */
    public function parse(\Iterator &$tokens): MatchNode
    {
        $matchKeywordToken = $this->extractMatchKeywordToken($tokens);
        $subject = $this->parseSubject($tokens);

        $this->skipOpeningBracketToken($tokens);

        try {
            $arms = $this->parseArms($tokens);

            Scanner::assertType($tokens, TokenType::BRACKET_CURLY_CLOSE);
            $closingBracketToken = $tokens->current();
            Scanner::skipOne($tokens);

            return new MatchNode(
                rangeInSource: Range::from(
                    $matchKeywordToken->boundaries->start,
                    $closingBracketToken->boundaries->end
                ),
                subject: $subject,
                arms: $arms
            );
        } catch (InvalidMatchArmNodes $e) {
            throw MatchCouldNotBeParsed::becauseOfInvalidMatchArmNodes(
                cause: $e,
                affectedRangeInSource: $matchKeywordToken->boundaries
            );
        }
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return Token
     */
    private function extractMatchKeywordToken(\Iterator &$tokens): Token
    {
        Scanner::assertType($tokens, TokenType::KEYWORD_MATCH);

        $matchKeywordToken = $tokens->current();

        Scanner::skipOne($tokens);
        Scanner::skipSpace($tokens);

        return $matchKeywordToken;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ExpressionNode
     */
    private function parseSubject(\Iterator &$tokens): ExpressionNode
    {
        $this->subjectParser ??= new ExpressionParser(
            stopAt: TokenType::BRACKET_CURLY_OPEN
        );

        return $this->subjectParser->parse($tokens);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return void
     */
    private function skipOpeningBracketToken(\Iterator &$tokens): void
    {
        Scanner::assertType($tokens, TokenType::BRACKET_CURLY_OPEN);
        Scanner::skipOne($tokens);
        Scanner::skipSpaceAndComments($tokens);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return MatchArmNodes
     */
    private function parseArms(\Iterator &$tokens): MatchArmNodes
    {
        $items = [];
        while (Scanner::type($tokens) !== TokenType::BRACKET_CURLY_CLOSE) {
            $items[] = $this->parseArm($tokens);
        }

        return new MatchArmNodes(...$items);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return MatchArmNode
     */
    private function parseArm(\Iterator &$tokens): MatchArmNode
    {
        $defaultKeywordToken = $this->extractDefaultKeywordToken($tokens);
        $left = is_null($defaultKeywordToken) ? $this->parseArmLeft($tokens) : null;

        $this->skipArrowSingleToken($tokens);

        $right = $this->parseArmRight($tokens);

        if (is_null($defaultKeywordToken)) {
            assert($left !== null);
            $start = $left->items[0]->rangeInSource->start;
        } else {
            $start = $defaultKeywordToken->boundaries->start;
        }

        return new MatchArmNode(
            rangeInSource: Range::from(
                $start,
                $right->rangeInSource->end
            ),
            left: $left,
            right: $right
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return null|Token
     */
    private function extractDefaultKeywordToken(\Iterator &$tokens): ?Token
    {
        if (Scanner::type($tokens) === TokenType::KEYWORD_DEFAULT) {
            $defaultKeywordToken = $tokens->current();
            Scanner::skipOne($tokens);
            Scanner::skipSpaceAndComments($tokens);

            return $defaultKeywordToken;
        }

        return null;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ExpressionNodes
     */
    private function parseArmLeft(\Iterator &$tokens): ExpressionNodes
    {
        $this->matchArmLeftParser ??= new ExpressionParser(
            stopAt: TokenType::ARROW_SINGLE
        );

        $items = [];
        while (Scanner::type($tokens) !== TokenType::ARROW_SINGLE) {
            assert($this->matchArmLeftParser !== null);
            $items[] = $this->matchArmLeftParser->parse($tokens);

            if (Scanner::type($tokens) !== TokenType::ARROW_SINGLE) {
                $this->skipCommaToken($tokens);
            }
        }

        return new ExpressionNodes(...$items);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return void
     */
    private function skipCommaToken(\Iterator &$tokens): void
    {
        Scanner::assertType($tokens, TokenType::COMMA);
        Scanner::skipOne($tokens);
        Scanner::skipSpaceAndComments($tokens);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return void
     */
    private function skipArrowSingleToken(\Iterator &$tokens): void
    {
        Scanner::assertType($tokens, TokenType::ARROW_SINGLE);
        Scanner::skipOne($tokens);
        Scanner::skipSpaceAndComments($tokens);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ExpressionNode
     */
    private function parseArmRight(\Iterator &$tokens): ExpressionNode
    {
        $this->matchArmRightParser ??= new ExpressionParser(
            stopAt: TokenType::BRACKET_CURLY_CLOSE
        );

        return $this->matchArmRightParser->parse($tokens);
    }
}
