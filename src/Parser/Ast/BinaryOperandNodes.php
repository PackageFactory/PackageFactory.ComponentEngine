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

namespace PackageFactory\ComponentEngine\Parser\Ast;

use PackageFactory\ComponentEngine\Definition\BinaryOperator;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;

/**
 * @implements \IteratorAggregate<ExpressionNode>
 */
final class BinaryOperandNodes implements \IteratorAggregate, \JsonSerializable
{
    private function __construct(
        public readonly ExpressionNode $first,
        public readonly ExpressionNode $second
    ) {
    }

    /**
     * @param ExpressionNode $first
     * @param \Iterator<mixed,Token> $tokens
     * @param BinaryOperator $operator
     * @return self
     */
    public static function fromTokens(ExpressionNode $first, \Iterator $tokens, BinaryOperator $operator): self
    {
        $precedence = $operator->toPrecedence();
        $operands = [$first];

        Scanner::skipSpaceAndComments($tokens);

        $operands[] = ExpressionNode::fromTokens($tokens, $precedence);

        Scanner::skipSpaceAndComments($tokens);

        return new self(...$operands);
    }

    public function getIterator(): \Traversable
    {
        yield $this->first;
        yield $this->second;
    }

    public function jsonSerialize(): mixed
    {
        return [$this->first, $this->second];
    }
}
