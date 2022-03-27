<?php

/**
 * PackageFactory.ComponentEngine - Universal View Components for PHP
 *   Copyright (C) 2022 Contributors of PackageFactory.ComponentEngine
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

namespace PackageFactory\ComponentEngine\Parser\Tokenizer\TokenStream;

use PackageFactory\ComponentEngine\Parser\Tokenizer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

/**
 * @implements \Iterator<mixed, Token>
 */
final class TokenStream implements \Iterator
{
    /**
     * @var \Iterator<Token>
     */
    private \Iterator $iterator;

    /**
     * @var array|Token[]
     */
    private array $lookAheadBuffer = [];

    private ?Token $last;

    private function __construct(private readonly Tokenizer $tokenizer)
    {
        $this->rewind();
    }

    public static function fromTokenizer(Tokenizer $tokenizer): self
    {
        return new self($tokenizer);
    }

    public function getLast(): ?Token
    {
        return $this->last;
    }

    public function lookAhead(int $length): ?Token
    {
        $count = count($this->lookAheadBuffer);

        if ($count > $length) {
            return $this->lookAheadBuffer[$length - 1];
        }

        $iterator = $this->iterator;
        $token = null;

        for ($i = 0; $i < $length - $count; $i++) {
            if (!$iterator->valid()) {
                return null;
            }

            $token = $iterator->current();
            $this->lookAheadBuffer[] = $token;
            $iterator->next();
        }

        return $token;
    }

    public function skip(int $length): void
    {
        for ($i = 0; $i < $length; $i++) {
            $this->next();
        }
    }

    public function skipWhiteSpaceAndComments(): void
    {
        while (
            match ($this->current()->type) {
                TokenType::WHITESPACE,
                TokenType::END_OF_LINE,
                TokenType::COMMENT => $this->valid(),
                default => false
            }
        ) {
            $this->next();
        }
    }

    /**
     * @return Token
     */
    public function current()
    {
        if (!$this->valid()) {
            throw TokenStreamInvariantViolation::becauseOfUnexpectedEndOfSource();
        }

        if ($this->lookAheadBuffer) {
            return $this->lookAheadBuffer[0];
        } else {
            return $this->iterator->current();
        }
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return $this->iterator->key();
    }

    public function next(): void
    {
        if ($this->lookAheadBuffer) {
            array_shift($this->lookAheadBuffer);
        } else {
            $this->iterator->next();
        }

        $this->last = $this->iterator->current();
    }

    public function rewind(): void
    {
        $this->iterator = $this->tokenizer->getIterator();
        $this->last = $this->iterator->current();
    }

    public function valid(): bool
    {
        return $this->iterator->valid();
    }
}
