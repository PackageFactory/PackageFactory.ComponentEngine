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
use PackageFactory\ComponentEngine\Language\Lexer\Lexer;
use PackageFactory\ComponentEngine\Language\Lexer\Rule\Rule;
use PackageFactory\ComponentEngine\Language\Lexer\Rule\Rules;
use PackageFactory\ComponentEngine\Language\Parser\IntegerLiteral\IntegerLiteralParser;
use PackageFactory\ComponentEngine\Language\Parser\StringLiteral\StringLiteralParser;
use PackageFactory\ComponentEngine\Parser\Source\Range;

final class EnumDeclarationParser
{
    use Singleton;

    private const RULES_ENUM_MEMBER_VALUE_START = [
        Rule::STRING_LITERAL_DELIMITER,
        Rule::INTEGER_BINARY,
        Rule::INTEGER_OCTAL,
        Rule::INTEGER_DECIMAL,
        Rule::INTEGER_HEXADECIMAL
    ];

    private ?StringLiteralParser $stringLiteralParser = null;
    private ?IntegerLiteralParser $integerLiteralParser = null;

    public function parse(Lexer $lexer): EnumDeclarationNode
    {
        $lexer->read(Rule::KEYWORD_ENUM);
        $start = $lexer->buffer->getStart();
        $lexer->skipSpace();

        $enumNameNode = $this->parseEnumName($lexer);
        $enumMemberDeclarations = $this->parseEnumMemberDeclarations($lexer);

        $end = $lexer->buffer->getEnd();

        return new EnumDeclarationNode(
            rangeInSource: Range::from($start, $end),
            name: $enumNameNode,
            members: $enumMemberDeclarations
        );
    }

    private function parseEnumName(Lexer $lexer): EnumNameNode
    {
        $lexer->read(Rule::WORD);
        $enumNameNode = new EnumNameNode(
            rangeInSource: $lexer->buffer->getRange(),
            value: EnumName::from($lexer->buffer->getContents())
        );
        $lexer->skipSpace();

        return $enumNameNode;
    }

    private function parseEnumMemberDeclarations(Lexer $lexer): EnumMemberDeclarationNodes
    {
        $lexer->read(Rule::BRACKET_CURLY_OPEN);
        $lexer->skipSpaceAndComments();

        $items = [];
        while (!$lexer->peek(Rule::BRACKET_CURLY_CLOSE)) {
            $items[] = $this->parseEnumMemberDeclaration($lexer);
        }

        $lexer->read(Rule::BRACKET_CURLY_CLOSE);

        return new EnumMemberDeclarationNodes(...$items);
    }

    private function parseEnumMemberDeclaration(Lexer $lexer): EnumMemberDeclarationNode
    {
        $name = $this->parseEnumMemberName($lexer);
        $value = $this->parseEnumMemberValue($lexer);

        $lexer->skipSpaceAndComments();

        return new EnumMemberDeclarationNode(
            rangeInSource: Range::from(
                $name->rangeInSource->start,
                $value?->rangeInSource->end
                    ?? $name->rangeInSource->end
            ),
            name: $name,
            value: $value
        );
    }

    private function parseEnumMemberName(Lexer $lexer): EnumMemberNameNode
    {
        $lexer->read(Rule::WORD);

        return new EnumMemberNameNode(
            rangeInSource: $lexer->buffer->getRange(),
            value: EnumMemberName::from($lexer->buffer->getContents())
        );
    }

    private function parseEnumMemberValue(Lexer $lexer): ?EnumMemberValueNode
    {
        if ($lexer->probe(Rule::BRACKET_ROUND_OPEN)) {
            $start = $lexer->buffer->getStart();

            $value = match ($lexer->expectOneOf(...self::RULES_ENUM_MEMBER_VALUE_START)) {
                Rule::STRING_LITERAL_DELIMITER =>
                    $this->parseStringLiteral($lexer),
                default =>
                  $this->parseIntegerLiteral($lexer)
            };

            $lexer->read(Rule::BRACKET_ROUND_CLOSE);
            $end = $lexer->buffer->getEnd();

            return new EnumMemberValueNode(
                rangeInSource: Range::from($start, $end),
                value: $value
            );
        }

        return null;
    }

    private function parseStringLiteral(Lexer $lexer): StringLiteralNode
    {
        $this->stringLiteralParser ??= StringLiteralParser::singleton();
        return $this->stringLiteralParser->parse($lexer);
    }

    private function parseIntegerLiteral(Lexer $lexer): IntegerLiteralNode
    {
        $this->integerLiteralParser ??= IntegerLiteralParser::singleton();
        return $this->integerLiteralParser->parse($lexer);
    }
}
