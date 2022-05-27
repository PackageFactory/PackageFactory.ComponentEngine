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

final class ParameterDeclarationNodes implements \JsonSerializable
{
    /**
     * @var array<string,ParameterDeclarationNode>
     */
    public readonly array $items;

    private function __construct(
        ParameterDeclarationNode ...$items
    ) {
        $this->items = $items;
    }

    public static function empty(): self
    {
        return new self();
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return self
     */
    public static function fromTokens(\Iterator $tokens): self
    {
        $result = self::empty();

        while (true) {
            Scanner::skipSpaceAndComments($tokens);

            switch (Scanner::type($tokens)) {
                case TokenType::BRACKET_ROUND_CLOSE:
                    break 2;
                case TokenType::STRING:
                    $result = $result->withAddedParameterDeclarationNode(
                        ParameterDeclarationNode::fromTokens($tokens)
                    );
                    break;
                default:
                    Scanner::assertType($tokens, TokenType::BRACKET_ROUND_CLOSE, TokenType::STRING);
            }

            Scanner::skipSpace($tokens);
            if (Scanner::type($tokens) === TokenType::BRACKET_ROUND_CLOSE) {
                break;
            }

            Scanner::assertType($tokens, TokenType::COMMA);
            Scanner::skipOne($tokens);
        }

        return $result;
    }

    public function withAddedParameterDeclarationNode(
        ParameterDeclarationNode $parameterDeclarationNode
    ): self {
        $name = $parameterDeclarationNode->name;

        if (array_key_exists($name, $this->items)) {
            throw new \Exception('@TODO: Duplicate Parameter Declaration ' . $name);
        }

        return new self(...$this->items, ...[$name => $parameterDeclarationNode]);
    }

    public function jsonSerialize(): mixed
    {
        return array_values($this->items);
    }
}
