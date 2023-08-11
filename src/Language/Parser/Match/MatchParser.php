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

use PackageFactory\ComponentEngine\Framework\PHP\Singleton\Singleton;
use PackageFactory\ComponentEngine\Language\AST\Node\Expression\ExpressionNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Expression\ExpressionNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Match\InvalidMatchArmNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Match\MatchArmNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Match\MatchArmNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Match\MatchNode;
use PackageFactory\ComponentEngine\Language\Lexer\Lexer;
use PackageFactory\ComponentEngine\Language\Lexer\Token\TokenType;
use PackageFactory\ComponentEngine\Language\Parser\Expression\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Source\Range;

final class MatchParser
{
    use Singleton;

    private ?ExpressionParser $subjectParser = null;
    private ?ExpressionParser $matchArmLeftParser = null;
    private ?ExpressionParser $matchArmRightParser = null;

    public function parse(Lexer $lexer): MatchNode
    {
        $lexer->read(TokenType::KEYWORD_MATCH);
        $start = $lexer->getStartPosition();
        $lexer->skipSpace();

        $subject = $this->parseSubject($lexer);
        $arms = $this->parseArms($lexer);
        $end = $lexer->getEndPosition();

        return new MatchNode(
            rangeInSource: Range::from($start, $end),
            subject: $subject,
            arms: $arms
        );
    }

    private function parseSubject(Lexer $lexer): ExpressionNode
    {
        $this->subjectParser ??= new ExpressionParser();

        return $this->subjectParser->parse($lexer);
    }

    private function parseArms(Lexer $lexer): MatchArmNodes
    {
        $lexer->read(TokenType::BRACKET_CURLY_OPEN);
        $start = $lexer->getStartPosition();

        $items = [];
        while (!$lexer->peek(TokenType::BRACKET_CURLY_CLOSE)) {
            $lexer->skipSpaceAndComments();
            $items[] = $this->parseArm($lexer);
        }


        $lexer->skipSpaceAndComments();
        $lexer->read(TokenType::BRACKET_CURLY_CLOSE);
        $end = $lexer->getEndPosition();

        try {
            return new MatchArmNodes(...$items);
        } catch (InvalidMatchArmNodes $e) {
            throw MatchCouldNotBeParsed::becauseOfInvalidMatchArmNodes(
                cause: $e,
                affectedRangeInSource: $e->affectedRangeInSource ?? Range::from($start, $end)
            );
        }
    }

    private function parseArm(Lexer $lexer): MatchArmNode
    {
        $left = $this->parseArmLeft($lexer);
        $start = $left?->items[0]?->rangeInSource->start ??
            $lexer->getStartPosition();

        $lexer->skipSpaceAndComments();
        $lexer->read(TokenType::SYMBOL_ARROW_SINGLE);
        $lexer->skipSpaceAndComments();

        $right = $this->parseArmRight($lexer);
        $lexer->skipSpaceAndComments();

        return new MatchArmNode(
            rangeInSource: Range::from($start, $right->rangeInSource->end),
            left: $left,
            right: $right
        );
    }

    private function parseArmLeft(Lexer $lexer): ?ExpressionNodes
    {
        if ($lexer->probe(TokenType::KEYWORD_DEFAULT)) {
            return null;
        }

        $this->matchArmLeftParser ??= new ExpressionParser();

        $items = [];
        do {
            $lexer->skipSpaceAndComments();
            $items[] = $this->matchArmLeftParser->parse($lexer);
            $lexer->skipSpaceAndComments();
        } while ($lexer->probe(TokenType::SYMBOL_COMMA));

        $lexer->skipSpaceAndComments();

        return new ExpressionNodes(...$items);
    }

    private function parseArmRight(Lexer $lexer): ExpressionNode
    {
        $this->matchArmRightParser ??= new ExpressionParser();

        return $this->matchArmRightParser->parse($lexer);
    }
}
