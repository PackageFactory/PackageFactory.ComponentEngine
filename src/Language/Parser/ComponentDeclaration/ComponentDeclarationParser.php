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
use PackageFactory\ComponentEngine\Framework\PHP\Singleton\Singleton;
use PackageFactory\ComponentEngine\Language\AST\Node\ComponentDeclaration\ComponentDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\ComponentDeclaration\ComponentNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Expression\ExpressionNode;
use PackageFactory\ComponentEngine\Language\AST\Node\PropertyDeclaration\PropertyDeclarationNodes;
use PackageFactory\ComponentEngine\Language\Lexer\Lexer;
use PackageFactory\ComponentEngine\Language\Lexer\Token\TokenType;
use PackageFactory\ComponentEngine\Language\Lexer\Token\TokenTypes;
use PackageFactory\ComponentEngine\Language\Parser\Expression\ExpressionParser;
use PackageFactory\ComponentEngine\Language\Parser\PropertyDeclaration\PropertyDeclarationParser;
use PackageFactory\ComponentEngine\Parser\Source\Range;

final class ComponentDeclarationParser
{
    use Singleton;

    private static TokenTypes $TOKEN_TYPES_SPACE;

    private ?PropertyDeclarationParser $propertyDeclarationParser = null;
    private ?ExpressionParser $returnParser = null;

    private function __construct()
    {
        self::$TOKEN_TYPES_SPACE ??= TokenTypes::from(
            TokenType::SPACE,
            TokenType::END_OF_LINE
        );
    }

    public function parse(Lexer $lexer): ComponentDeclarationNode
    {
        $lexer->read(TokenType::KEYWORD_COMPONENT);
        $start = $lexer->getStartPosition();
        $lexer->skipSpace();

        $name = $this->parseName($lexer);
        $props = $this->parseProps($lexer);
        $return = $this->parseReturn($lexer);

        $lexer->read(TokenType::BRACKET_CURLY_CLOSE);
        $end = $lexer->getEndPosition();

        return new ComponentDeclarationNode(
            rangeInSource: Range::from($start, $end),
            name: $name,
            props: $props,
            return: $return
        );
    }

    private function parseName(Lexer $lexer): ComponentNameNode
    {
        $lexer->read(TokenType::WORD);
        $componentNameToken = $lexer->getTokenUnderCursor();

        $lexer->skipSpace();

        return new ComponentNameNode(
            rangeInSource: $componentNameToken->rangeInSource,
            value: ComponentName::from($componentNameToken->value)
        );
    }

    private function parseProps(Lexer $lexer): PropertyDeclarationNodes
    {
        $this->propertyDeclarationParser ??= PropertyDeclarationParser::singleton();

        $lexer->read(TokenType::BRACKET_CURLY_OPEN);
        $lexer->skipSpaceAndComments();

        $items = [];
        while (!$lexer->peek(TokenType::KEYWORD_RETURN)) {
            $lexer->expect(TokenType::WORD);
            $items[] = $this->propertyDeclarationParser->parse($lexer);
            $lexer->skipSpaceAndComments();
        }

        return new PropertyDeclarationNodes(...$items);
    }

    private function parseReturn(Lexer $lexer): ExpressionNode
    {
        $this->returnParser ??= new ExpressionParser();

        $lexer->read(TokenType::KEYWORD_RETURN);
        $lexer->readOneOf(self::$TOKEN_TYPES_SPACE);
        $lexer->skipSpaceAndComments();

        return $this->returnParser->parse($lexer);
    }
}
