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

use LogicException;
use PackageFactory\ComponentEngine\Framework\PHP\Singleton\Singleton;
use PackageFactory\ComponentEngine\Language\AST\Node\ComponentDeclaration\ComponentDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Export\ExportNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StructDeclaration\StructDeclarationNode;
use PackageFactory\ComponentEngine\Language\Lexer\Lexer;
use PackageFactory\ComponentEngine\Language\Lexer\LexerException;
use PackageFactory\ComponentEngine\Language\Lexer\Rule\Rule;
use PackageFactory\ComponentEngine\Language\Parser\ComponentDeclaration\ComponentDeclarationParser;
use PackageFactory\ComponentEngine\Language\Parser\EnumDeclaration\EnumDeclarationParser;
use PackageFactory\ComponentEngine\Language\Parser\StructDeclaration\StructDeclarationParser;
use PackageFactory\ComponentEngine\Parser\Source\Range;

final class ExportParser
{
    use Singleton;

    private const RULES_DECLARATION_KEYWORDS = [
        Rule::KEYWORD_COMPONENT,
        Rule::KEYWORD_ENUM,
        Rule::KEYWORD_STRUCT
    ];

    private ?ComponentDeclarationParser $componentDeclarationParser = null;
    private ?EnumDeclarationParser $enumDeclarationParser = null;
    private ?StructDeclarationParser $structDeclarationParser = null;

    public function parse(Lexer $lexer): ExportNode
    {
        try {
            $lexer->read(Rule::KEYWORD_EXPORT);
            $start = $lexer->buffer->getStart();

            $lexer->skipSpace();

            $declaration = match ($lexer->expect(...self::RULES_DECLARATION_KEYWORDS)) {
                Rule::KEYWORD_COMPONENT => $this->parseComponentDeclaration($lexer),
                Rule::KEYWORD_ENUM => $this->parseEnumDeclaration($lexer),
                Rule::KEYWORD_STRUCT => $this->parseStructDeclaration($lexer),
                default => throw new LogicException()
            };

            $end = $lexer->buffer->getEnd();

            return new ExportNode(
                rangeInSource: Range::from($start, $end),
                declaration: $declaration
            );
        } catch (LexerException $e) {
            throw ExportCouldNotBeParsed::becauseOfLexerException($e);
        }
    }

    private function parseComponentDeclaration(Lexer $lexer): ComponentDeclarationNode
    {
        $this->componentDeclarationParser ??= ComponentDeclarationParser::singleton();
        return $this->componentDeclarationParser->parse($lexer);
    }

    private function parseEnumDeclaration(Lexer $lexer): EnumDeclarationNode
    {
        $this->enumDeclarationParser ??= EnumDeclarationParser::singleton();
        return $this->enumDeclarationParser->parse($lexer);
    }

    private function parseStructDeclaration(Lexer $lexer): StructDeclarationNode
    {
        $this->structDeclarationParser ??= StructDeclarationParser::singleton();
        return $this->structDeclarationParser->parse($lexer);
    }
}
