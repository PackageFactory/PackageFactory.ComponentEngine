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

namespace PackageFactory\ComponentEngine\Language\Parser\Text;

use PackageFactory\ComponentEngine\Framework\PHP\Singleton\Singleton;
use PackageFactory\ComponentEngine\Language\AST\Node\Text\TextNode;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class TextParser
{
    use Singleton;

    private string $value;

    private ?Token $startingToken;
    private ?Token $finalToken;

    private bool $trimLeadingSpace;
    private bool $trimTrailingSpace;
    private bool $currentlyCapturingSpace;
    private bool $trailingSpaceContainsLineBreak;
    private bool $terminated;

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @param boolean $preserveLeadingSpace
     * @return null|TextNode
     */
    public function parse(\Iterator &$tokens, bool $preserveLeadingSpace = false): ?TextNode
    {
        $this->reset($preserveLeadingSpace);

        while (!Scanner::isEnd($tokens) && !$this->terminated) {
            $this->startingToken ??= $tokens->current();

            match (Scanner::type($tokens)) {
                TokenType::BRACKET_CURLY_OPEN,
                TokenType::TAG_START_OPENING =>
                    $this->terminateAtAdjacentChildNode(),
                TokenType::TAG_START_CLOSING =>
                    $this->terminateAtClosingTag(),
                TokenType::SPACE =>
                    $this->captureSpace($tokens->current()),
                TokenType::END_OF_LINE =>
                    $this->captureLineBreak($tokens->current()),
                default =>
                    $this->captureText($tokens->current()),
            };

            if (!$this->terminated) {
                Scanner::skipOne($tokens);
            }
        }

        return $this->build();
    }

    private function reset(bool $preserveLeadingSpace): void
    {
        $this->value = '';

        $this->startingToken = null;
        $this->finalToken = null;

        $this->trimLeadingSpace = !$preserveLeadingSpace;
        $this->trimTrailingSpace = true;
        $this->currentlyCapturingSpace = false;
        $this->trailingSpaceContainsLineBreak = false;
        $this->terminated = false;
    }

    private function terminateAtAdjacentChildNode(): void
    {
        $this->terminated = true;
        $this->trimTrailingSpace = $this->trailingSpaceContainsLineBreak;
    }

    private function terminateAtClosingTag(): void
    {
        $this->terminated = true;
    }

    private function captureSpace(Token $token): void
    {
        $this->finalToken = $token;

        if ($this->currentlyCapturingSpace) {
            return;
        }

        $this->currentlyCapturingSpace = true;
        $this->value .= ' ';
    }

    private function captureLineBreak(Token $token): void
    {
        $this->captureSpace($token);
        $this->trailingSpaceContainsLineBreak = true;
    }

    private function captureText(Token $token): void
    {
        $this->finalToken = $token;
        $this->currentlyCapturingSpace = false;
        $this->trailingSpaceContainsLineBreak = false;

        $this->value .= $token->value;
    }

    private function build(): ?TextNode
    {
        if (is_null($this->startingToken) || is_null($this->finalToken)) {
            return null;
        }

        if ($this->trimLeadingSpace) {
            $this->value = ltrim($this->value);
        }

        if ($this->trimTrailingSpace) {
            $this->value = rtrim($this->value);
        }

        if ($this->value === '' || $this->value === ' ') {
            return null;
        }

        return new TextNode(
            rangeInSource: Range::from(
                $this->startingToken->boundaries->start,
                $this->finalToken->boundaries->end
            ),
            value: $this->value
        );
    }
}
