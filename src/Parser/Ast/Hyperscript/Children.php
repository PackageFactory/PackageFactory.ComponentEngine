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

namespace PackageFactory\ComponentEngine\Parser\Ast\Hyperscript;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Expression;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class Children implements \JsonSerializable
{
    /**
     * @var array<int,Text|Expression|Tag>
     */
    private readonly array $children;

    private function __construct(
        Text | Expression | Tag ...$children
    ) {
        $this->children = $children;
    }

    public static function empty()
    {
        return new self();
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return self
     */
    public static function fromTokens(\Iterator $tokens): self
    {
        Scanner::skipSpaceAndComments($tokens);

        /** @var array<string,Text|Expression|Tag> $contents */
        $contents = [];
        while (true) {
            if ($text = Text::fromTokens($tokens)) {
                $contents[] = $text;
            }

            switch (Scanner::type($tokens)) {
                case TokenType::TAG_START_CLOSING:
                    break 2;
                case TokenType::TAG_START_OPENING:
                    $contents[] = Tag::fromTokens($tokens);
                    break;
                case TokenType::BRACKET_OPEN:
                    Scanner::assertValue($tokens, '{');
                    Scanner::skipOne($tokens);
                    $contents[] = Expression::fromTokens($tokens);
                    Scanner::skipSpaceAndComments($tokens);
                    Scanner::assertType($tokens, TokenType::BRACKET_CLOSE);
                    Scanner::assertValue($tokens, '}');
                    Scanner::skipOne($tokens);
                    Scanner::skipSpace($tokens);
                default:
                    Scanner::assertType($tokens, TokenType::TAG_START_CLOSING, TokenType::TAG_START_OPENING, TokenType::BRACKET_OPEN);
            }
        }

        return new self(...$contents);
    }

    public function jsonSerialize(): mixed
    {
        return $this->children;
    }
}
