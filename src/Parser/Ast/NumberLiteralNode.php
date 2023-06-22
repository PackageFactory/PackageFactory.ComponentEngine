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

use PackageFactory\ComponentEngine\Definition\NumberFormat;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;

final class NumberLiteralNode implements \JsonSerializable
{
    public function __construct(
        public readonly string $value,
        public readonly NumberFormat $format
    ) {
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return self
     */
    public static function fromTokens(\Iterator $tokens): self
    {
        $format = NumberFormat::fromTokenType(Scanner::type($tokens));
        $value = Scanner::value($tokens);

        Scanner::skipOne($tokens);

        return new self(
            value: $value,
            format: $format
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => 'NumberLiteralNode',
            'payload' => [
                'value' => $this->value,
                'format' => $this->format->value
            ]
        ];
    }
}
