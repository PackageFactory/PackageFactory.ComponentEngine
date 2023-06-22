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

use PackageFactory\ComponentEngine\Parser\Parser\ComponentDeclaration\ComponentDeclarationParser;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class ComponentDeclarationNode implements \JsonSerializable
{
    public function __construct(
        public readonly string $componentName,
        public readonly PropertyDeclarationNodes $propertyDeclarations,
        public readonly ExpressionNode $returnExpression
    ) {
    }

    public static function fromString(string $componentDeclarationAsString): self
    {
        return ComponentDeclarationParser::parseFromString($componentDeclarationAsString);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return self
     */
    public static function fromTokens(\Iterator $tokens): self
    {
        Scanner::skipSpaceAndComments($tokens);
        Scanner::assertType($tokens, TokenType::KEYWORD_COMPONENT);

        Scanner::skipOne($tokens);
        Scanner::skipSpaceAndComments($tokens);
        Scanner::assertType($tokens, TokenType::STRING);

        $componentName = Scanner::value($tokens);

        Scanner::skipOne($tokens);
        Scanner::skipSpaceAndComments($tokens);
        Scanner::assertType($tokens, TokenType::BRACKET_CURLY_OPEN);

        Scanner::skipOne($tokens);
        Scanner::skipSpaceAndComments($tokens);

        $propertyDeclarations = PropertyDeclarationNodes::fromTokens($tokens);

        Scanner::skipSpaceAndComments($tokens);
        Scanner::assertType($tokens, TokenType::KEYWORD_RETURN);
        Scanner::skipOne($tokens);
        Scanner::skipSpaceAndComments($tokens);

        $returnExpression = ExpressionNode::fromTokens($tokens);

        Scanner::skipSpaceAndComments($tokens);
        Scanner::assertType($tokens, TokenType::BRACKET_CURLY_CLOSE);
        Scanner::skipOne($tokens);

        return new self(
            componentName: $componentName,
            propertyDeclarations: $propertyDeclarations,
            returnExpression: $returnExpression
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => 'ComponentDeclarationNode',
            'payload' => [
                'componentName' => $this->componentName,
                'propertyDeclarations' => $this->propertyDeclarations,
                'returnExpression' => $this->returnExpression
            ]
        ];
    }
}
