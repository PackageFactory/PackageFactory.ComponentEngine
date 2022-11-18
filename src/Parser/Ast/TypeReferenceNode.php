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

use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class TypeReferenceNode implements \JsonSerializable
{
    private function __construct(
        public readonly string $name,
        public readonly bool $isArray,
        public readonly bool $isOptional
    ) {
    }

    public static function fromString(string $typeReferenceAsString): self
    {
        return self::fromTokens(
            Tokenizer::fromSource(
                Source::fromString($typeReferenceAsString)
            )->getIterator()
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return self
     */
    public static function fromTokens(\Iterator $tokens): self
    {
        Scanner::skipSpaceAndComments($tokens);

        if (Scanner::type($tokens) === TokenType::QUESTIONMARK) {
            $isOptional = true;
            Scanner::skipOne($tokens);
        } else {
            $isOptional = false;
        }

        Scanner::assertType($tokens, TokenType::STRING);

        $name = Scanner::value($tokens);

        Scanner::skipOne($tokens);

        $isArray = !Scanner::isEnd($tokens) && Scanner::type($tokens) === TokenType::BRACKET_SQUARE_OPEN;
        if ($isArray) {
            Scanner::skipOne($tokens);
            Scanner::skipSpace($tokens);
            Scanner::assertType($tokens, TokenType::BRACKET_SQUARE_CLOSE);
            Scanner::skipOne($tokens);
        }

        return new self(
            name: $name,
            isArray: $isArray,
            isOptional: $isOptional
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => 'TypeReferenceNode',
            'payload' => [
                'name' => $this->name,
                'isArray' => $this->isArray,
                'isOptional' => $this->isOptional
            ]
        ];
    }
}
