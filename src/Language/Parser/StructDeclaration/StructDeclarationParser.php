<?php

/**
 * PackageFactory.ComponentEngine - Universal View Components for PHP
 *   Copyright (C) 2023 Contributors of PackageFactory.ComponentEngine
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

namespace PackageFactory\ComponentEngine\Language\Parser\StructDeclaration;

use PackageFactory\ComponentEngine\Domain\StructName\StructName;
use PackageFactory\ComponentEngine\Language\AST\Node\PropertyDeclaration\PropertyDeclarationNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\StructDeclaration\StructDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StructDeclaration\StructNameNode;
use PackageFactory\ComponentEngine\Language\Parser\PropertyDeclaration\PropertyDeclarationParser;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class StructDeclarationParser
{
    private readonly PropertyDeclarationParser $propertyDeclarationParser;

    public function __construct()
    {
        $this->propertyDeclarationParser = new PropertyDeclarationParser();
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return StructDeclarationNode
     */
    public function parse(\Iterator $tokens): StructDeclarationNode
    {
        $structKeywordToken = $this->extractStructKeywordToken($tokens);
        $structNameNode = $this->parseStructName($tokens);
        $this->skipOpeningBracketToken($tokens);
        $propertyDeclarationNodes = $this->parsePropertyDeclarations($tokens);
        $closingBracketToken = $this->extractClosingBracketToken($tokens);

        return new StructDeclarationNode(
            rangeInSource: Range::from(
                $structKeywordToken->boundaries->start,
                $closingBracketToken->boundaries->end
            ),
            name: $structNameNode,
            properties: $propertyDeclarationNodes
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return Token
     */
    public function extractStructKeywordToken(\Iterator $tokens): Token
    {
        Scanner::assertType($tokens, TokenType::KEYWORD_STRUCT);

        $structKeywordToken = $tokens->current();

        Scanner::skipOne($tokens);
        Scanner::skipSpace($tokens);

        return $structKeywordToken;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return StructNameNode
     */
    public function parseStructName(\Iterator $tokens): StructNameNode
    {
        Scanner::assertType($tokens, TokenType::STRING);

        $structNameToken = $tokens->current();

        Scanner::skipOne($tokens);
        Scanner::skipSpaceAndComments($tokens);

        return new StructNameNode(
            rangeInSource: $structNameToken->boundaries,
            value: StructName::from($structNameToken->value)
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return void
     */
    public function skipOpeningBracketToken(\Iterator $tokens): void
    {
        Scanner::assertType($tokens, TokenType::BRACKET_CURLY_OPEN);
        Scanner::skipOne($tokens);
        Scanner::skipSpaceAndComments($tokens);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return PropertyDeclarationNodes
     */
    public function parsePropertyDeclarations(\Iterator $tokens): PropertyDeclarationNodes
    {
        $items = [];
        while (Scanner::type($tokens) === TokenType::STRING) {
            $items[] = $this->propertyDeclarationParser->parse($tokens);
            Scanner::skipSpaceAndComments($tokens);
        }

        return new PropertyDeclarationNodes(...$items);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return Token
     */
    public function extractClosingBracketToken(\Iterator $tokens): Token
    {
        Scanner::assertType($tokens, TokenType::BRACKET_CURLY_CLOSE);

        $closingBracketToken = $tokens->current();

        Scanner::skipOne($tokens);

        return $closingBracketToken;
    }
}
