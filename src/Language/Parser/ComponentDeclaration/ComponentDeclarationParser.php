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

namespace PackageFactory\ComponentEngine\Language\Parser\ComponentDeclaration;

use PackageFactory\ComponentEngine\Domain\ComponentName\ComponentName;
use PackageFactory\ComponentEngine\Language\AST\Node\ComponentDeclaration\ComponentDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\ComponentDeclaration\ComponentNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Expression\ExpressionNode;
use PackageFactory\ComponentEngine\Language\AST\Node\PropertyDeclaration\PropertyDeclarationNodes;
use PackageFactory\ComponentEngine\Language\Parser\Expression\ExpressionParser;
use PackageFactory\ComponentEngine\Language\Parser\PropertyDeclaration\PropertyDeclarationParser;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class ComponentDeclarationParser
{
    private readonly PropertyDeclarationParser $propertyDeclarationParser;
    private ?ExpressionParser $returnParser = null;

    public function __construct()
    {
        $this->propertyDeclarationParser = new PropertyDeclarationParser();
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ComponentDeclarationNode
     */
    public function parse(\Iterator &$tokens): ComponentDeclarationNode
    {
        $componentKeywordToken = $this->extractComponentKeywordToken($tokens);
        $name = $this->parseName($tokens);

        $this->skipOpeningBracketToken($tokens);

        $props = $this->parseProps($tokens);

        $this->skipReturnKeywordToken($tokens);

        $return = $this->parseReturn($tokens);
        $closingBracketToken = $this->extractClosingBracketToken($tokens);

        return new ComponentDeclarationNode(
            rangeInSource: Range::from(
                $componentKeywordToken->boundaries->start,
                $closingBracketToken->boundaries->end
            ),
            name: $name,
            props: $props,
            return: $return
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return Token
     */
    private function extractComponentKeywordToken(\Iterator &$tokens): Token
    {
        Scanner::assertType($tokens, TokenType::KEYWORD_COMPONENT);

        $componentKeywordToken = $tokens->current();

        Scanner::skipOne($tokens);
        Scanner::skipSpace($tokens);

        return $componentKeywordToken;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ComponentNameNode
     */
    private function parseName(\Iterator &$tokens): ComponentNameNode
    {
        Scanner::assertType($tokens, TokenType::STRING);

        $componentNameToken = $tokens->current();

        Scanner::skipOne($tokens);
        Scanner::skipSpace($tokens);

        return new ComponentNameNode(
            rangeInSource: $componentNameToken->boundaries,
            value: ComponentName::from($componentNameToken->value)
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return void
     */
    private function skipOpeningBracketToken(\Iterator &$tokens): void
    {
        Scanner::assertType($tokens, TokenType::BRACKET_CURLY_OPEN);
        Scanner::skipOne($tokens);
        Scanner::skipSpaceAndComments($tokens);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return PropertyDeclarationNodes
     */
    private function parseProps(\Iterator &$tokens): PropertyDeclarationNodes
    {
        $items = [];
        while (Scanner::type($tokens) !== TokenType::KEYWORD_RETURN) {
            $items[] = $this->propertyDeclarationParser->parse($tokens);

            Scanner::skipSpaceAndComments($tokens);
        }

        return new PropertyDeclarationNodes(...$items);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return void
     */
    private function skipReturnKeywordToken(\Iterator &$tokens): void
    {
        Scanner::assertType($tokens, TokenType::KEYWORD_RETURN);
        Scanner::skipOne($tokens);
        Scanner::skipSpaceAndComments($tokens);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ExpressionNode
     */
    private function parseReturn(\Iterator &$tokens): ExpressionNode
    {
        $this->returnParser ??= new ExpressionParser(
            stopAt: TokenType::BRACKET_CURLY_CLOSE
        );

        return $this->returnParser->parse($tokens);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return Token
     */
    private function extractClosingBracketToken(\Iterator &$tokens): Token
    {
        Scanner::assertType($tokens, TokenType::BRACKET_CURLY_CLOSE);

        $closingBracketToken = $tokens->current();

        Scanner::skipOne($tokens);

        return $closingBracketToken;
    }
}
