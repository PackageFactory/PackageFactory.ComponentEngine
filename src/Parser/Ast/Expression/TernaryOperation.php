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

namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class TernaryOperation implements \JsonSerializable
{
    private function __construct(
        public readonly Expression $condition,
        public readonly Expression $true,
        public readonly Expression $false
    ) {
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return self
     */
    public static function fromTokens(Expression $condition, \Iterator $tokens): self
    {
        Scanner::skipSpace($tokens);
        Scanner::assertType($tokens, TokenType::QUESTIONMARK);
        Scanner::skipOne($tokens);

        $true = Expression::fromTokens($tokens, Precedence::TERNARY);

        Scanner::skipSpace($tokens);
        Scanner::assertType($tokens, TokenType::COLON);
        Scanner::skipOne($tokens);

        $false = Expression::fromTokens($tokens, Precedence::TERNARY);

        return new self(
            condition: $condition,
            true: $true,
            false: $false
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => 'TernaryOperation',
            'payload' => [
                'condition' => $this->condition,
                'true' => $this->true,
                'false' => $this->false
            ]
        ];
    }
}