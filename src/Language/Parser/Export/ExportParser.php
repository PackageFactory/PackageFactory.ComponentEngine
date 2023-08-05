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

namespace PackageFactory\ComponentEngine\Language\Parser\Export;

use PackageFactory\ComponentEngine\Framework\PHP\Singleton\Singleton;
use PackageFactory\ComponentEngine\Language\AST\Node\ComponentDeclaration\ComponentDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Export\ExportNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StructDeclaration\StructDeclarationNode;
use PackageFactory\ComponentEngine\Language\Parser\ComponentDeclaration\ComponentDeclarationParser;
use PackageFactory\ComponentEngine\Language\Parser\EnumDeclaration\EnumDeclarationParser;
use PackageFactory\ComponentEngine\Language\Parser\StructDeclaration\StructDeclarationParser;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenTypes;

final class ExportParser
{
    use Singleton;

    private ?ComponentDeclarationParser $componentDeclarationParser = null;
    private ?EnumDeclarationParser $enumDeclarationParser = null;
    private ?StructDeclarationParser $structDeclarationParser = null;

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ExportNode
     */
    public function parse(\Iterator &$tokens): ExportNode
    {
        $exportKeywordToken = $this->extractToken($tokens, TokenType::KEYWORD_EXPORT);
        $declaration = match (Scanner::type($tokens)) {
            TokenType::KEYWORD_COMPONENT => $this->parseComponentDeclaration($tokens),
            TokenType::KEYWORD_ENUM => $this->parseEnumDeclaration($tokens),
            TokenType::KEYWORD_STRUCT => $this->parseStructDeclaration($tokens),
            default => throw ExportCouldNotBeParsed::becauseOfUnexpectedToken(
                expectedTokenTypes: TokenTypes::from(
                    TokenType::KEYWORD_COMPONENT,
                    TokenType::KEYWORD_ENUM,
                    TokenType::KEYWORD_STRUCT
                ),
                actualToken: $tokens->current()
            )
        };

        return new ExportNode(
            rangeInSource: Range::from(
                $exportKeywordToken->boundaries->start,
                $declaration->rangeInSource->end
            ),
            declaration: $declaration
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @param TokenType $tokenType
     * @return Token
     */
    private function extractToken(\Iterator &$tokens, TokenType $tokenType): Token
    {
        Scanner::assertType($tokens, $tokenType);
        $token = $tokens->current();
        Scanner::skipOne($tokens);
        Scanner::skipSpace($tokens);

        return $token;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ComponentDeclarationNode
     */
    private function parseComponentDeclaration(\Iterator &$tokens): ComponentDeclarationNode
    {
        $this->componentDeclarationParser ??= ComponentDeclarationParser::singleton();
        return $this->componentDeclarationParser->parse($tokens);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return EnumDeclarationNode
     */
    private function parseEnumDeclaration(\Iterator &$tokens): EnumDeclarationNode
    {
        $this->enumDeclarationParser ??= EnumDeclarationParser::singleton();
        return $this->enumDeclarationParser->parse($tokens);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return StructDeclarationNode
     */
    private function parseStructDeclaration(\Iterator &$tokens): StructDeclarationNode
    {
        $this->structDeclarationParser ??= StructDeclarationParser::singleton();
        return $this->structDeclarationParser->parse($tokens);
    }
}
