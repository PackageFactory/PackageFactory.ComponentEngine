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

use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class TagNode implements \JsonSerializable
{
    private function __construct(
        public readonly string $tagName,
        public readonly AttributeNodes $attributes,
        public readonly TagContentNodes $children,
        public readonly bool $isSelfClosing
    ) {
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return self
     */
    public static function fromTokens(\Iterator $tokens): self
    {
        Scanner::skipSpaceAndComments($tokens);
        Scanner::assertType($tokens, TokenType::TAG_START_OPENING);

        Scanner::skipOne($tokens);
        Scanner::assertType($tokens, TokenType::STRING);

        $tagName = Scanner::value($tokens);

        Scanner::skipOne($tokens);

        $attributes = AttributeNodes::fromTokens($tokens);

        Scanner::skipSpaceAndComments($tokens);

        if (Scanner::type($tokens) === TokenType::TAG_SELF_CLOSE) {
            Scanner::skipOne($tokens);

            return new self(
                tagName: $tagName,
                attributes: $attributes,
                children: TagContentNodes::empty(),
                isSelfClosing: true
            );
        }

        Scanner::assertType($tokens, TokenType::TAG_END);
        Scanner::skipOne($tokens);
        Scanner::skipSpace($tokens);

        $children = TagContentNodes::fromTokens($tokens);

        Scanner::assertType($tokens, TokenType::TAG_START_CLOSING);
        Scanner::skipOne($tokens);
        Scanner::assertType($tokens, TokenType::STRING);
        Scanner::assertValue($tokens, $tagName);
        Scanner::skipOne($tokens);
        Scanner::skipSpaceAndComments($tokens);
        Scanner::assertType($tokens, TokenType::TAG_END);
        Scanner::skipOne($tokens);

        return new self(
            tagName: $tagName,
            attributes: $attributes,
            children: $children,
            isSelfClosing: false
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => 'TagNode',
            'payload' => [
                'tagName' => $this->tagName,
                'attributes' => $this->attributes,
                'children' => $this->children,
                'isSelfClosing' => $this->isSelfClosing
            ]
        ];
    }
}
