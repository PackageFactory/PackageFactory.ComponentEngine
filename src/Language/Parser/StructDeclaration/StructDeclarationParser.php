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
use PackageFactory\ComponentEngine\Framework\PHP\Singleton\Singleton;
use PackageFactory\ComponentEngine\Language\AST\Node\PropertyDeclaration\PropertyDeclarationNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\StructDeclaration\StructDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StructDeclaration\StructNameNode;
use PackageFactory\ComponentEngine\Language\Lexer\Lexer;
use PackageFactory\ComponentEngine\Language\Lexer\Token\TokenType;
use PackageFactory\ComponentEngine\Language\Parser\PropertyDeclaration\PropertyDeclarationParser;
use PackageFactory\ComponentEngine\Parser\Source\Range;

final class StructDeclarationParser
{
    use Singleton;

    private ?PropertyDeclarationParser $propertyDeclarationParser = null;

    public function parse(Lexer $lexer): StructDeclarationNode
    {
        $lexer->read(TokenType::KEYWORD_STRUCT);
        $start = $lexer->getStartPosition();
        $lexer->skipSpace();

        $structNameNode = $this->parseStructName($lexer);
        $propertyDeclarationNodes = $this->parsePropertyDeclarations($lexer);
        $end = $lexer->getEndPosition();

        return new StructDeclarationNode(
            rangeInSource: Range::from($start, $end),
            name: $structNameNode,
            properties: $propertyDeclarationNodes
        );
    }

    private function parseStructName(Lexer $lexer): StructNameNode
    {
        $lexer->read(TokenType::WORD);
        $structNameNode = new StructNameNode(
            rangeInSource: $lexer->getCursorRange(),
            value: StructName::from($lexer->getBuffer())
        );

        $lexer->skipSpaceAndComments();

        return $structNameNode;
    }

    public function parsePropertyDeclarations(Lexer $lexer): PropertyDeclarationNodes
    {
        $this->propertyDeclarationParser ??= PropertyDeclarationParser::singleton();

        $lexer->read(TokenType::BRACKET_CURLY_OPEN);
        $lexer->skipSpaceAndComments();

        $items = [];
        while (!$lexer->probe(TokenType::BRACKET_CURLY_CLOSE)) {
            $lexer->expect(TokenType::WORD);
            $items[] = $this->propertyDeclarationParser->parse($lexer);
            $lexer->skipSpaceAndComments();
        }

        return new PropertyDeclarationNodes(...$items);
    }
}
