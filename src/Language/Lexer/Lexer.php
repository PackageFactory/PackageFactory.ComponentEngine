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

namespace PackageFactory\ComponentEngine\Language\Lexer;

use LogicException;
use PackageFactory\ComponentEngine\Language\Lexer\CharacterStream\CharacterStream;
use PackageFactory\ComponentEngine\Language\Lexer\Matcher\Matcher;
use PackageFactory\ComponentEngine\Language\Lexer\Matcher\Result;
use PackageFactory\ComponentEngine\Language\Lexer\Rule\Token;
use PackageFactory\ComponentEngine\Language\Lexer\Rule\Rule;
use PackageFactory\ComponentEngine\Language\Lexer\Rule\Rules;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Range;

final class Lexer
{
    private readonly Rules $TOKEN_TYPES_SPACE;
    private readonly Rules $TOKEN_TYPES_SPACE_AND_COMMENTS;

    private readonly CharacterStream $characterStream;
    private Position $startPosition;
    private int $offset = 0;
    private string $buffer = '';
    private ?Rule $tokenTypeUnderCursor = null;

    public function __construct(string $source)
    {
        $this->TOKEN_TYPES_SPACE = Rules::from(
            Rule::SPACE,
            Rule::END_OF_LINE
        );
        $this->TOKEN_TYPES_SPACE_AND_COMMENTS = Rules::from(
            Rule::SPACE,
            Rule::END_OF_LINE,
            Rule::COMMENT
        );

        $this->characterStream = new CharacterStream($source);
        $this->startPosition = Position::zero();
    }

    public function getRuleUnderCursor(): Rule
    {
        assert($this->tokenTypeUnderCursor !== null);

        return $this->tokenTypeUnderCursor;
    }

    public function getBuffer(): string
    {
        return $this->buffer;
    }

    public function isEnd(): bool
    {
        return $this->characterStream->isEnd();
    }

    public function assertIsEnd(): void
    {
        if (!$this->isEnd()) {
            throw LexerException::becauseOfUnexpectedExceedingSource(
                affectedRangeInSource: $this->characterStream->getCurrentPosition()->toRange(),
                exceedingCharacter: $this->characterStream->current() ?? ''
            );
        }
    }

    public function getStartPosition(): Position
    {
        return $this->startPosition;
    }

    public function getEndPosition(): Position
    {
        return $this->characterStream->getPreviousPosition();
    }

    public function getCursorRange(): Range
    {
        return $this->getStartPosition()->toRange($this->getEndPosition());
    }

    public function read(Rule $tokenType): void
    {

        if ($this->characterStream->isEnd()) {
            throw LexerException::becauseOfUnexpectedEndOfSource(
                expectedRules: Rules::from($tokenType),
                affectedRangeInSource: $this->characterStream->getCurrentPosition()->toRange()
            );
        }

        if ($this->extract($tokenType)) {
            $this->tokenTypeUnderCursor = $tokenType;
            return;
        }

        throw LexerException::becauseOfUnexpectedCharacterSequence(
            expectedRules: Rules::from($tokenType),
            affectedRangeInSource: Range::from(
                $this->startPosition,
                $this->characterStream->getCurrentPosition()
            ),
            actualCharacterSequence: $this->buffer . $this->characterStream->current()
        );
    }

    public function readOneOf(Rules $tokenTypes): void
    {

        if ($this->characterStream->isEnd()) {
            throw LexerException::becauseOfUnexpectedEndOfSource(
                expectedRules: $tokenTypes,
                affectedRangeInSource: $this->characterStream->getCurrentPosition()->toRange()
            );
        }

        $foundRule = $this->extractOneOf($tokenTypes);
        if ($foundRule === null) {
            throw LexerException::becauseOfUnexpectedCharacterSequence(
                expectedRules: $tokenTypes,
                affectedRangeInSource: Range::from(
                    $this->startPosition,
                    $this->characterStream->getPreviousPosition()
                ),
                actualCharacterSequence: $this->buffer
            );
        }

        $this->tokenTypeUnderCursor = $foundRule;
    }

    public function probe(Rule $tokenType): bool
    {

        if ($this->characterStream->isEnd()) {
            return false;
        }

        $snapshot = $this->characterStream->makeSnapshot();

        if ($tokenType = $this->extract($tokenType)) {
            $this->tokenTypeUnderCursor = $tokenType;
            return true;
        }

        $this->characterStream->restoreSnapshot($snapshot);
        return false;
    }

    public function probeOneOf(Rules $tokenTypes): bool
    {
        if ($this->characterStream->isEnd()) {
            return false;
        }

        $snapshot = $this->characterStream->makeSnapshot();

        if ($tokenType = $this->extractOneOf($tokenTypes)) {
            $this->tokenTypeUnderCursor = $tokenType;
            return true;
        }

        $this->characterStream->restoreSnapshot($snapshot);
        return false;
    }

    public function peek(Rule $tokenType): bool
    {
        if ($this->characterStream->isEnd()) {
            return false;
        }

        $snapshot = $this->characterStream->makeSnapshot();
        $result = $this->extract($tokenType) !== null;
        $this->characterStream->restoreSnapshot($snapshot);

        return $result;
    }

    public function peekOneOf(Rules $tokenTypes): ?Rule
    {
        if ($this->characterStream->isEnd()) {
            return null;
        }

        $snapshot = $this->characterStream->makeSnapshot();
        $foundRule = $this->extractOneOf($tokenTypes);
        $this->characterStream->restoreSnapshot($snapshot);

        return $foundRule;
    }

    public function expect(Rule $tokenType): void
    {
        if ($this->characterStream->isEnd()) {
            throw LexerException::becauseOfUnexpectedEndOfSource(
                expectedRules: Rules::from($tokenType),
                affectedRangeInSource: $this->characterStream->getCurrentPosition()->toRange()
            );
        }

        $snapshot = $this->characterStream->makeSnapshot();
        if ($this->extract($tokenType) === null) {
            throw LexerException::becauseOfUnexpectedCharacterSequence(
                expectedRules: Rules::from($tokenType),
                affectedRangeInSource: Range::from(
                    $this->startPosition,
                    $this->characterStream->getPreviousPosition()
                ),
                actualCharacterSequence: $this->buffer
            );
        }

        $this->characterStream->restoreSnapshot($snapshot);
    }

    public function expectOneOf(Rules $tokenTypes): Rule
    {
        if ($this->characterStream->isEnd()) {
            throw LexerException::becauseOfUnexpectedEndOfSource(
                expectedRules: $tokenTypes,
                affectedRangeInSource: $this->characterStream->getCurrentPosition()->toRange()
            );
        }

        $snapshot = $this->characterStream->makeSnapshot();
        $foundRule = $this->extractOneOf($tokenTypes);
        if ($foundRule === null) {
            throw LexerException::becauseOfUnexpectedCharacterSequence(
                expectedRules: $tokenTypes,
                affectedRangeInSource: Range::from(
                    $this->startPosition,
                    $this->characterStream->getPreviousPosition()
                ),
                actualCharacterSequence: $this->buffer
            );
        }

        $this->characterStream->restoreSnapshot($snapshot);

        return $foundRule;
    }

    public function skipSpace(): void
    {
        $this->skipAnyOf($this->TOKEN_TYPES_SPACE);
    }

    public function skipSpaceAndComments(): void
    {
        $this->skipAnyOf($this->TOKEN_TYPES_SPACE_AND_COMMENTS);
    }

    private function skipAnyOf(Rules $tokenTypes): void
    {
        while (true) {
            $character = $this->characterStream->current();

            foreach ($tokenTypes->items as $tokenType) {
                $matcher = Matcher::for($tokenType);

                if ($matcher->match($character, 0) === Result::KEEP) {
                    $this->read($tokenType);
                    continue 2;
                }
            }

            break;
        }
    }

    private function extract(Rule $tokenType): ?Rule
    {
        $this->startPosition = $this->characterStream->getCurrentPosition();
        $this->offset = 0;
        $this->buffer = '';

        while (true) {
            $character = $this->characterStream->current();
            $result = Matcher::for($tokenType)->match($character, $this->offset);

            if ($result === Result::SATISFIED) {
                return $tokenType;
            }

            if ($result === Result::CANCEL) {
                return null;
            }

            $this->offset++;
            $this->buffer .= $character;
            $this->characterStream->next();
        }
    }

    private function extractOneOf(Rules $tokenTypes): ?Rule
    {
        $this->startPosition = $this->characterStream->getCurrentPosition();
        $this->offset = 0;
        $this->buffer = '';

        $tokenTypeCandidates = $tokenTypes->items;
        while (count($tokenTypeCandidates)) {
            $character = $this->characterStream->current();

            $nextRuleCandidates = [];
            foreach ($tokenTypeCandidates as $tokenType) {
                $result = Matcher::for($tokenType)->match($character, $this->offset);

                if ($result === Result::SATISFIED) {
                    return $tokenType;
                }

                if ($result === Result::KEEP) {
                    $nextRuleCandidates[] = $tokenType;
                }
            }

            $this->offset++;
            $this->buffer .= $character;
            $tokenTypeCandidates = $nextRuleCandidates;
            $this->characterStream->next();
        }

        return null;
    }

    public function dumpRest(): string
    {
        return $this->characterStream->getRest();
    }
}
