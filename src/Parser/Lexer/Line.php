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

namespace PackageFactory\ComponentEngine\Parser\Lexer;

/**
 * @implements \IteratorAggregate<mixed, Token>
 */
final class Line implements \IteratorAggregate
{
    /**
     * @param int $number
     * @param array|Token[] $tokens
     */
    private function __construct(
        public readonly int $number,
        private readonly array $tokens
    ) {
    }

    public static function fromTokenStream(int $number, TokenStream $stream): self
    {
        $tokens = [];
        while ($stream->valid()) {
            $token = $stream->current();
            $stream->next();

            if ($token->type === TokenType::END_OF_LINE) {
                break;
            } else {
                $tokens[] = $token;
            }
        }

        return new self($number, $tokens);
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @return \Traversable<Token>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->tokens);
    }
}
