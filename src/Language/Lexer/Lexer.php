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
    private readonly CharacterStream $characterStream;
    private ?Position $startPosition = null;
    private int $offset = 0;
    private string $buffer = '';
    private ?TokenType $tokenTypeUnderCursor = null;
    private ?Token $tokenUnderCursor = null;
    private ?LexerException $latestError = null;

    public function __construct(string $source)
    {
        $this->characterStream = new CharacterStream($source);
    }

    public function read(TokenType $tokenType): void
    {
        assert($this->latestError === null);
        $this->startPosition = $this->characterStream->getCurrentPosition();

        if ($this->characterStream->isEnd()) {
            throw $this->latestError = LexerException::becauseOfUnexpectedEndOfSource(
                expectedTokenTypes: TokenTypes::from($tokenType),
                affectedRangeInSource: $this->startPosition->toRange()
            );
        }

        $this->tokenTypeUnderCursor = null;
        $this->tokenUnderCursor = null;
        $this->offset = 0;
        $this->buffer = '';

        while (true) {
            $character = $this->characterStream->current();
            $result = Matcher::for($tokenType)->match($character, $this->offset);

            if ($result === Result::KEEP) {
                $this->offset++;
                $this->buffer .= $character;
                $this->characterStream->next();
                continue;
            }

            if ($result === Result::SATISFIED) {
                $this->tokenTypeUnderCursor = $tokenType;
                break;
            }

            if ($result === Result::CANCEL) {
                throw $this->latestError = LexerException::becauseOfUnexpectedCharacterSequence(
                    expectedTokenTypes: TokenTypes::from($tokenType),
                    affectedRangeInSource: Range::from(
                        $this->startPosition,
                        $this->characterStream->getCurrentPosition()
                    ),
                    actualCharacterSequence: $this->buffer . $character
                );
            }
        }
    }

    public function readOneOf(TokenTypes $tokenTypes): void
    {
        assert($this->latestError === null);
        $this->startPosition = $this->characterStream->getCurrentPosition();

        if ($this->characterStream->isEnd()) {
            throw $this->latestError = LexerException::becauseOfUnexpectedEndOfSource(
                expectedTokenTypes: $tokenTypes,
                affectedRangeInSource: $this->startPosition->toRange()
            );
        }

        $this->tokenTypeUnderCursor = null;
        $this->tokenUnderCursor = null;
        $this->offset = 0;
        $this->buffer = '';

        $tokenTypeCandidates = $tokenTypes->items;
        while (count($tokenTypeCandidates)) {
            $character = $this->characterStream->current();

            $nextTokenTypeCandidates = [];
            foreach ($tokenTypeCandidates as $tokenType) {
                $result = Matcher::for($tokenType)->match($character, $this->offset);

                if ($result === Result::KEEP) {
                    $nextTokenTypeCandidates[] = $tokenType;
                    continue;
                }

                if ($result === Result::SATISFIED) {
                    $this->tokenTypeUnderCursor = $tokenType;
                    return;
                }
            }

            $this->offset++;
            $this->buffer .= $character;
            $tokenTypeCandidates = $nextTokenTypeCandidates;
            $this->characterStream->next();
        }

        throw $this->latestError = LexerException::becauseOfUnexpectedCharacterSequence(
            expectedTokenTypes: $tokenTypes,
            affectedRangeInSource: Range::from(
                $this->startPosition,
                $this->characterStream->getPreviousPosition()
            ),
            actualCharacterSequence: $this->buffer
        );
    }

    public function skipSpace(): void
    {
        assert($this->latestError === null);
        $this->skip(TokenType::SPACE, TokenType::END_OF_LINE);
    }

    public function skipSpaceAndComments(): void
    {
        assert($this->latestError === null);
        $this->skip(TokenType::SPACE, TokenType::END_OF_LINE, TokenType::COMMENT);
    }

    private function skip(TokenType ...$tokenTypes): void
    {
        while (true) {
            $character = $this->characterStream->current();

            foreach ($tokenTypes as $tokenType) {
                $matcher = Matcher::for($tokenType);

                if ($matcher->match($character, 0) === Result::KEEP) {
                    $this->read($tokenType);
                    continue 2;
                }
            }

            break;
        }
    }

    public function getTokenUnderCursor(): Token
    {
        assert($this->latestError === null);
        assert($this->startPosition !== null);
        assert($this->tokenTypeUnderCursor !== null);

        return $this->tokenUnderCursor ??= new Token(
            rangeInSource: Range::from(
                $this->startPosition,
                $this->characterStream->getPreviousPosition()
            ),
            type: $this->tokenTypeUnderCursor,
            value: $this->buffer
        );
    }

    public function isEnd(): bool
    {
        return $this->characterStream->isEnd();
    }
}
