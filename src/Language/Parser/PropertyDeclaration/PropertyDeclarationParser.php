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

namespace PackageFactory\ComponentEngine\Language\Parser\PropertyDeclaration;

use PackageFactory\ComponentEngine\Domain\PropertyName\PropertyName;
use PackageFactory\ComponentEngine\Framework\PHP\Singleton\Singleton;
use PackageFactory\ComponentEngine\Language\AST\Node\PropertyDeclaration\PropertyDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\PropertyDeclaration\PropertyNameNode;
use PackageFactory\ComponentEngine\Language\Lexer\Lexer;
use PackageFactory\ComponentEngine\Language\Lexer\Token\TokenType;
use PackageFactory\ComponentEngine\Language\Parser\TypeReference\TypeReferenceParser;
use PackageFactory\ComponentEngine\Parser\Source\Range;

final class PropertyDeclarationParser
{
    use Singleton;

    private ?TypeReferenceParser $typeReferenceParser = null;

    public function parse(Lexer $lexer): PropertyDeclarationNode
    {
        $lexer->read(TokenType::WORD);
        $propertyNameToken = $lexer->getTokenUnderCursor();

        $lexer->read(TokenType::SYMBOL_COLON);
        $lexer->skipSpace();

        $this->typeReferenceParser ??= TypeReferenceParser::singleton();
        $typeReferenceNode = $this->typeReferenceParser->parse($lexer);

        return new PropertyDeclarationNode(
            rangeInSource: Range::from(
                $propertyNameToken->rangeInSource->start,
                $typeReferenceNode->rangeInSource->end
            ),
            name: new PropertyNameNode(
                rangeInSource: $propertyNameToken->rangeInSource,
                value: PropertyName::from($propertyNameToken->value)
            ),
            type: $typeReferenceNode
        );
    }
}
