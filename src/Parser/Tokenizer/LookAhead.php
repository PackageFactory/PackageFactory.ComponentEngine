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

namespace PackageFactory\ComponentEngine\Parser\Tokenizer;

/**
 * @implements \IteratorAggregate<mixed,Token>
 */
final class LookAhead implements  \IteratorAggregate
{
    /**
     * @var Token[]
     */
    private array $buffer = [];

    /**
     * @param \Iterator<Token> $tokens
     */
    private function __construct(
        public readonly \Iterator $tokens
    ) {
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return self
     */
    public static function fromTokens(\Iterator $tokens): self
    {
        return new self(tokens: $tokens);
    }

    /**
     * @return \Iterator<mixed,Token>
     */
    public function getIterator(): \Iterator
    {
        foreach ($this->buffer as $token) {
            yield $token;
        }

        if (!Scanner::isEnd($this->tokens)) {
            yield from $this->tokens;
        }
    }

    public function shift(): void
    {
        Scanner::assertValid($this->tokens);
        $this->buffer[] = $this->tokens->current();
        Scanner::skipOne($this->tokens);
    }

    public function type(): ?TokenType
    {
        if (Scanner::isEnd($this->tokens)) {
            return null;
        }
        return Scanner::type($this->tokens);
    }
}
