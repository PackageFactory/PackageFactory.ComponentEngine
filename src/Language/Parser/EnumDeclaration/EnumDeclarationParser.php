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

namespace PackageFactory\ComponentEngine\Language\Parser\EnumDeclaration;

use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumMemberDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumMemberDeclarationNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumMemberName;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumName;
use PackageFactory\ComponentEngine\Language\Parser\IntegerLiteral\IntegerLiteralParser;
use PackageFactory\ComponentEngine\Language\Parser\StringLiteral\StringLiteralParser;
use PackageFactory\ComponentEngine\Language\AST\NodeAttributes\NodeAttributes;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class EnumDeclarationParser
{
    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return EnumDeclarationNode
     */
    public function parse(\Iterator $tokens): EnumDeclarationNode
    {
        Scanner::assertType($tokens, TokenType::KEYWORD_ENUM);

        $enumKeyWordToken = $tokens->current();

        Scanner::skipOne($tokens);
        Scanner::skipSpaceAndComments($tokens);
        Scanner::assertType($tokens, TokenType::STRING);

        $enumKeyNameToken = $tokens->current();
        $enumName = EnumName::from($enumKeyNameToken->value);

        Scanner::skipOne($tokens);
        Scanner::skipSpace($tokens);
        Scanner::assertType($tokens, TokenType::BRACKET_CURLY_OPEN);
        Scanner::skipOne($tokens);

        $enumMemberDeclarations = $this->parseEnumMemberDeclarations($tokens);

        Scanner::skipSpace($tokens);
        Scanner::assertType($tokens, TokenType::BRACKET_CURLY_CLOSE);

        $closingBracketToken = $tokens->current();

        Scanner::skipOne($tokens);

        return new EnumDeclarationNode(
            attributes: new NodeAttributes(
                pathToSource: $enumKeyWordToken->sourcePath,
                rangeInSource: Range::from(
                    $enumKeyWordToken->boundaries->start,
                    $closingBracketToken->boundaries->end
                )
            ),
            enumName: $enumName,
            memberDeclarations: $enumMemberDeclarations
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return EnumMemberDeclarationNodes
     */
    private function parseEnumMemberDeclarations(\Iterator $tokens): EnumMemberDeclarationNodes
    {
        $items = [];
        while (true) {
            Scanner::skipSpaceAndComments($tokens);

            switch (Scanner::type($tokens)) {
                case TokenType::STRING:
                    $items[] = $this->parseEnumMemberDeclarationNode($tokens);
                    break;
                case TokenType::BRACKET_CURLY_CLOSE:
                    break 2;
                default:
                    Scanner::assertType($tokens, TokenType::STRING, TokenType::BRACKET_CURLY_CLOSE);
            }
        }

        return new EnumMemberDeclarationNodes(...$items);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return EnumMemberDeclarationNode
     */
    private function parseEnumMemberDeclarationNode(\Iterator $tokens): EnumMemberDeclarationNode
    {
        Scanner::skipSpaceAndComments($tokens);
        Scanner::assertType($tokens, TokenType::STRING);

        $enumMemberNameToken = $finalToken = $tokens->current();
        $enumMemberName = EnumMemberName::from($enumMemberNameToken->value);

        Scanner::skipOne($tokens);

        $value = null;
        if (Scanner::type($tokens) === TokenType::BRACKET_ROUND_OPEN) {
            Scanner::skipOne($tokens);
            $valueToken = $tokens->current();
            $value = match ($valueToken->type) {
                TokenType::STRING_QUOTED =>
                    (new StringLiteralParser())->parse($tokens),
                TokenType::NUMBER_BINARY,
                TokenType::NUMBER_OCTAL,
                TokenType::NUMBER_DECIMAL,
                TokenType::NUMBER_HEXADECIMAL =>
                    (new IntegerLiteralParser())->parse($tokens),
                default => throw new \Exception('@TODO: Unexpected Token ' . Scanner::type($tokens)->value)
            };
            Scanner::assertType($tokens, TokenType::BRACKET_ROUND_CLOSE);
            $finalToken = $tokens->current();

            Scanner::skipOne($tokens);
        }

        return new EnumMemberDeclarationNode(
            attributes: new NodeAttributes(
                pathToSource: $enumMemberNameToken->sourcePath,
                rangeInSource: Range::from(
                    $enumMemberNameToken->boundaries->start,
                    $finalToken->boundaries->end
                )
            ),
            name: $enumMemberName,
            value: $value
        );
    }
}
