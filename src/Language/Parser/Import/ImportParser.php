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

namespace PackageFactory\ComponentEngine\Language\Parser\Import;

use PackageFactory\ComponentEngine\Domain\VariableName\VariableName;
use PackageFactory\ComponentEngine\Language\AST\Node\Import\ImportedNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Import\ImportedNameNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Import\ImportNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Import\InvalidImportedNameNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\StringLiteral\StringLiteralNode;
use PackageFactory\ComponentEngine\Language\Parser\StringLiteral\StringLiteralParser;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class ImportParser
{
    private readonly StringLiteralParser $pathParser;

    public function __construct()
    {
        $this->pathParser = new StringLiteralParser();
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ImportNode
     */
    public function parse(\Iterator &$tokens): ImportNode
    {
        $fromKeywordToken = $this->extractToken($tokens, TokenType::KEYWORD_FROM);
        $path = $this->parsePath($tokens);

        $this->skipToken($tokens, TokenType::KEYWORD_IMPORT);
        $openingBracketToken = $this->extractToken($tokens, TokenType::BRACKET_CURLY_OPEN);

        try {
            $names = $this->parseNames($tokens);
            $closingBracketToken = $this->extractToken($tokens, TokenType::BRACKET_CURLY_CLOSE);

            return new ImportNode(
                rangeInSource: Range::from(
                    $fromKeywordToken->boundaries->start,
                    $closingBracketToken->boundaries->end
                ),
                path: $path,
                names: $names
            );
        } catch (InvalidImportedNameNodes $e) {
            throw ImportCouldNotBeParsed::becauseOfInvalidImportedNameNodes(
                cause: $e,
                affectedRangeInSource: $openingBracketToken->boundaries
            );
        }
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
     * @param TokenType $tokenType
     * @return void
     */
    private function skipToken(\Iterator &$tokens, TokenType $tokenType): void
    {
        Scanner::assertType($tokens, $tokenType);
        Scanner::skipOne($tokens);
        Scanner::skipSpace($tokens);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return StringLiteralNode
     */
    private function parsePath(\Iterator &$tokens): StringLiteralNode
    {
        $path = $this->pathParser->parse($tokens);
        Scanner::skipSpace($tokens);

        return $path;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ImportedNameNodes
     */
    private function parseNames(\Iterator &$tokens): ImportedNameNodes
    {
        $items = [];
        while (Scanner::type($tokens) !== TokenType::BRACKET_CURLY_CLOSE) {
            $items[] = $this->parseName($tokens);

            if (Scanner::type($tokens) !== TokenType::BRACKET_CURLY_CLOSE) {
                $this->skipToken($tokens, TokenType::COMMA);
            }
        }

        return new ImportedNameNodes(...$items);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ImportedNameNode
     */
    private function parseName(\Iterator &$tokens): ImportedNameNode
    {
        $nameToken = $this->extractToken($tokens, TokenType::STRING);

        return new ImportedNameNode(
            rangeInSource: $nameToken->boundaries,
            value: VariableName::from($nameToken->value)
        );
    }
}
