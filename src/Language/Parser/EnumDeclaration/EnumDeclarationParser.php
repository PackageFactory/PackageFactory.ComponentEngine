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

use PackageFactory\ComponentEngine\Domain\EnumMemberName\EnumMemberName;
use PackageFactory\ComponentEngine\Domain\EnumName\EnumName;
use PackageFactory\ComponentEngine\Framework\PHP\Singleton\Singleton;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumMemberDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumMemberDeclarationNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumMemberNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumMemberValueNode;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\IntegerLiteral\IntegerLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StringLiteral\StringLiteralNode;
use PackageFactory\ComponentEngine\Language\Parser\IntegerLiteral\IntegerLiteralParser;
use PackageFactory\ComponentEngine\Language\Parser\StringLiteral\StringLiteralParser;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class EnumDeclarationParser
{
    use Singleton;

    private ?StringLiteralParser $stringLiteralParser = null;
    private ?IntegerLiteralParser $integerLiteralParser = null;

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return EnumDeclarationNode
     */
    public function parse(\Iterator &$tokens): EnumDeclarationNode
    {
        $enumKeyWordToken = $this->extractEnumKeywordToken($tokens);
        $enumNameNode = $this->parseEnumName($tokens);

        $this->skipOpeningBracketToken($tokens);

        $enumMemberDeclarations = $this->parseEnumMemberDeclarations($tokens);
        $closingBracketToken = $this->extractClosingBracketToken($tokens);

        return new EnumDeclarationNode(
            rangeInSource: Range::from(
                $enumKeyWordToken->boundaries->start,
                $closingBracketToken->boundaries->end
            ),
            name: $enumNameNode,
            members: $enumMemberDeclarations
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return Token
     */
    private function extractEnumKeywordToken(\Iterator &$tokens): Token
    {
        Scanner::assertType($tokens, TokenType::KEYWORD_ENUM);

        $enumKeyWordToken = $tokens->current();

        Scanner::skipOne($tokens);
        Scanner::skipSpace($tokens);

        return $enumKeyWordToken;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return EnumNameNode
     */
    private function parseEnumName(\Iterator &$tokens): EnumNameNode
    {
        Scanner::assertType($tokens, TokenType::STRING);

        $enumKeyNameToken = $tokens->current();
        $enumNameNode = new EnumNameNode(
            rangeInSource: $enumKeyNameToken->boundaries,
            value: EnumName::from($enumKeyNameToken->value)
        );

        Scanner::skipOne($tokens);
        Scanner::skipSpace($tokens);

        return $enumNameNode;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return void
     */
    private function skipOpeningBracketToken(\Iterator &$tokens): void
    {
        Scanner::assertType($tokens, TokenType::BRACKET_CURLY_OPEN);
        Scanner::skipOne($tokens);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return EnumMemberDeclarationNodes
     */
    private function parseEnumMemberDeclarations(\Iterator &$tokens): EnumMemberDeclarationNodes
    {
        $items = [];
        while (true) {
            Scanner::skipSpaceAndComments($tokens);

            switch (Scanner::type($tokens)) {
                case TokenType::STRING:
                    $items[] = $this->parseEnumMemberDeclaration($tokens);
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
     * @return Token
     */
    private function extractClosingBracketToken(\Iterator &$tokens): Token
    {
        Scanner::skipSpace($tokens);
        Scanner::assertType($tokens, TokenType::BRACKET_CURLY_CLOSE);

        $closingBracketToken = $tokens->current();

        Scanner::skipOne($tokens);

        return $closingBracketToken;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return EnumMemberDeclarationNode
     */
    private function parseEnumMemberDeclaration(\Iterator &$tokens): EnumMemberDeclarationNode
    {
        $enumMemberName = $this->parseEnumMemberName($tokens);
        $value = $this->parseEnumMemberValue($tokens);

        return new EnumMemberDeclarationNode(
            rangeInSource: Range::from(
                $enumMemberName->rangeInSource->start,
                $value?->rangeInSource->end
                    ?? $enumMemberName->rangeInSource->end
            ),
            name: $enumMemberName,
            value: $value
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return EnumMemberNameNode
     */
    private function parseEnumMemberName(\Iterator &$tokens): EnumMemberNameNode
    {
        Scanner::assertType($tokens, TokenType::STRING);

        $enumMemberNameToken = $tokens->current();
        $enumMemberNameNode = new EnumMemberNameNode(
            rangeInSource: $enumMemberNameToken->boundaries,
            value: EnumMemberName::from($enumMemberNameToken->value)
        );

        Scanner::skipOne($tokens);

        return $enumMemberNameNode;
    }

    /**
     * @param \Iterator $tokens
     * @return null|EnumMemberValueNode
     */
    private function parseEnumMemberValue(\Iterator &$tokens): ?EnumMemberValueNode
    {
        if (Scanner::type($tokens) !== TokenType::BRACKET_ROUND_OPEN) {
            return null;
        }

        $openingBracketToken = $tokens->current();
        Scanner::skipOne($tokens);

        $valueToken = $tokens->current();
        $value = match ($valueToken->type) {
            TokenType::STRING_QUOTED =>
                $this->parseStringLiteral($tokens),
            TokenType::NUMBER_BINARY,
            TokenType::NUMBER_OCTAL,
            TokenType::NUMBER_DECIMAL,
            TokenType::NUMBER_HEXADECIMAL =>
                $this->parseIntegerLiteral($tokens),
            default => throw new \Exception('@TODO: Unexpected Token ' . Scanner::type($tokens)->value)
        };

        Scanner::assertType($tokens, TokenType::BRACKET_ROUND_CLOSE);
        $closingBracketToken = $tokens->current();
        Scanner::skipOne($tokens);

        return new EnumMemberValueNode(
            rangeInSource: Range::from(
                $openingBracketToken->boundaries->start,
                $closingBracketToken->boundaries->end
            ),
            value: $value
        );
    }

    /**
     * @param \Iterator $tokens
     * @return StringLiteralNode
     */
    private function parseStringLiteral(\Iterator &$tokens): StringLiteralNode
    {
        $this->stringLiteralParser ??= StringLiteralParser::singleton();
        return $this->stringLiteralParser->parse($tokens);
    }

    /**
     * @param \Iterator $tokens
     * @return IntegerLiteralNode
     */
    private function parseIntegerLiteral(\Iterator &$tokens): IntegerLiteralNode
    {
        $this->integerLiteralParser ??= IntegerLiteralParser::singleton();
        return $this->integerLiteralParser->parse($tokens);
    }
}
