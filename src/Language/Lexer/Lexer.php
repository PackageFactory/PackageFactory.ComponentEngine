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
use PackageFactory\ComponentEngine\Language\Lexer\Token\Token;
use PackageFactory\ComponentEngine\Language\Lexer\Token\TokenType;
use PackageFactory\ComponentEngine\Language\Lexer\Token\TokenTypes;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Range;

final class Lexer
{
    private readonly TokenTypes $TOKEN_TYPES_SPACE;
    private readonly TokenTypes $TOKEN_TYPES_SPACE_AND_COMMENTS;

    private readonly CharacterStream $characterStream;
    private Position $startPosition;
    private int $offset = 0;
    private string $buffer = '';
    private ?TokenType $tokenTypeUnderCursor = null;
    private ?Token $tokenUnderCursor = null;

    public function __construct(string $source)
    {
        $this->TOKEN_TYPES_SPACE = TokenTypes::from(
            TokenType::SPACE,
            TokenType::END_OF_LINE
        );
        $this->TOKEN_TYPES_SPACE_AND_COMMENTS = TokenTypes::from(
            TokenType::SPACE,
            TokenType::END_OF_LINE,
            TokenType::COMMENT
        );

        $this->characterStream = new CharacterStream($source);
        $this->startPosition = Position::zero();
    }

    public function getTokenTypeUnderCursor(): TokenType
    {
        assert($this->tokenTypeUnderCursor !== null);

        return $this->tokenTypeUnderCursor;
    }

    public function getTokenUnderCursor(): Token
    {
        return $this->tokenUnderCursor ??= new Token(
            rangeInSource: Range::from($this->startPosition, $this->getEndPosition()),
            type: $this->getTokenTypeUnderCursor(),
            value: $this->buffer
        );
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

    public function read(TokenType $tokenType): void
    {

        if ($this->characterStream->isEnd()) {
            throw LexerException::becauseOfUnexpectedEndOfSource(
                expectedTokenTypes: TokenTypes::from($tokenType),
                affectedRangeInSource: $this->characterStream->getCurrentPosition()->toRange()
            );
        }

        if ($this->extract($tokenType)) {
            $this->tokenTypeUnderCursor = $tokenType;
            return;
        }

        throw LexerException::becauseOfUnexpectedCharacterSequence(
            expectedTokenTypes: TokenTypes::from($tokenType),
            affectedRangeInSource: Range::from(
                $this->startPosition,
                $this->characterStream->getCurrentPosition()
            ),
            actualCharacterSequence: $this->buffer . $this->characterStream->current()
        );
    }

    public function readOneOf(TokenTypes $tokenTypes): void
    {

        if ($this->characterStream->isEnd()) {
            throw LexerException::becauseOfUnexpectedEndOfSource(
                expectedTokenTypes: $tokenTypes,
                affectedRangeInSource: $this->characterStream->getCurrentPosition()->toRange()
            );
        }

        $foundTokenType = $this->extractOneOf($tokenTypes);
        if ($foundTokenType === null) {
            throw LexerException::becauseOfUnexpectedCharacterSequence(
                expectedTokenTypes: $tokenTypes,
                affectedRangeInSource: Range::from(
                    $this->startPosition,
                    $this->characterStream->getPreviousPosition()
                ),
                actualCharacterSequence: $this->buffer
            );
        }

        $this->tokenTypeUnderCursor = $foundTokenType;
    }

    public function probe(TokenType $tokenType): bool
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

    public function probeOneOf(TokenTypes $tokenTypes): bool
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

    public function peek(TokenType $tokenType): bool
    {
        if ($this->characterStream->isEnd()) {
            return false;
        }

        $snapshot = $this->characterStream->makeSnapshot();
        $result = $this->extract($tokenType) !== null;
        $this->characterStream->restoreSnapshot($snapshot);

        return $result;
    }

    public function peekOneOf(TokenTypes $tokenTypes): ?TokenType
    {
        if ($this->characterStream->isEnd()) {
            return null;
        }

        $snapshot = $this->characterStream->makeSnapshot();
        $foundTokenType = $this->extractOneOf($tokenTypes);
        $this->characterStream->restoreSnapshot($snapshot);

        return $foundTokenType;
    }

    public function expect(TokenType $tokenType): void
    {
        if ($this->characterStream->isEnd()) {
            throw LexerException::becauseOfUnexpectedEndOfSource(
                expectedTokenTypes: TokenTypes::from($tokenType),
                affectedRangeInSource: $this->characterStream->getCurrentPosition()->toRange()
            );
        }

        $snapshot = $this->characterStream->makeSnapshot();
        if ($this->extract($tokenType) === null) {
            throw LexerException::becauseOfUnexpectedCharacterSequence(
                expectedTokenTypes: TokenTypes::from($tokenType),
                affectedRangeInSource: Range::from(
                    $this->startPosition,
                    $this->characterStream->getPreviousPosition()
                ),
                actualCharacterSequence: $this->buffer
            );
        }

        $this->characterStream->restoreSnapshot($snapshot);
    }

    public function expectOneOf(TokenTypes $tokenTypes): TokenType
    {
        if ($this->characterStream->isEnd()) {
            throw LexerException::becauseOfUnexpectedEndOfSource(
                expectedTokenTypes: $tokenTypes,
                affectedRangeInSource: $this->characterStream->getCurrentPosition()->toRange()
            );
        }

        $snapshot = $this->characterStream->makeSnapshot();
        $foundTokenType = $this->extractOneOf($tokenTypes);
        if ($foundTokenType === null) {
            throw LexerException::becauseOfUnexpectedCharacterSequence(
                expectedTokenTypes: $tokenTypes,
                affectedRangeInSource: Range::from(
                    $this->startPosition,
                    $this->characterStream->getPreviousPosition()
                ),
                actualCharacterSequence: $this->buffer
            );
        }

        $this->characterStream->restoreSnapshot($snapshot);

        return $foundTokenType;
    }

    public function skipSpace(): void
    {
        $this->skipAnyOf($this->TOKEN_TYPES_SPACE);
    }

    public function skipSpaceAndComments(): void
    {
        $this->skipAnyOf($this->TOKEN_TYPES_SPACE_AND_COMMENTS);
    }

    private function skipAnyOf(TokenTypes $tokenTypes): void
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

    private function extract(TokenType $tokenType): ?TokenType
    {
        $this->startPosition = $this->characterStream->getCurrentPosition();
        $this->tokenUnderCursor = null;
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

    private function extractOneOf(TokenTypes $tokenTypes): ?TokenType
    {
        $this->startPosition = $this->characterStream->getCurrentPosition();
        $this->tokenUnderCursor = null;
        $this->offset = 0;
        $this->buffer = '';

        $tokenTypeCandidates = $tokenTypes->items;
        while (count($tokenTypeCandidates)) {
            $character = $this->characterStream->current();

            $nextTokenTypeCandidates = [];
            foreach ($tokenTypeCandidates as $tokenType) {
                $result = Matcher::for($tokenType)->match($character, $this->offset);

                if ($result === Result::SATISFIED) {
                    return $tokenType;
                }

                if ($result === Result::KEEP) {
                    $nextTokenTypeCandidates[] = $tokenType;
                }
            }

            $this->offset++;
            $this->buffer .= $character;
            $tokenTypeCandidates = $nextTokenTypeCandidates;
            $this->characterStream->next();
        }

        return null;
    }

    public function dumpRest(): string
    {
        return $this->characterStream->getRest();
    }
}
