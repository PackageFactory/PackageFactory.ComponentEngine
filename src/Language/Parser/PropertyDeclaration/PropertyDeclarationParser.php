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
use PackageFactory\ComponentEngine\Language\AST\Node\PropertyDeclaration\PropertyDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\PropertyDeclaration\PropertyNameNode;
use PackageFactory\ComponentEngine\Language\Parser\TypeReference\TypeReferenceParser;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class PropertyDeclarationParser
{
    private readonly TypeReferenceParser $typeReferenceParser;

    public function __construct()
    {
        $this->typeReferenceParser = new TypeReferenceParser();
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return PropertyDeclarationNode
     */
    public function parse(\Iterator &$tokens): PropertyDeclarationNode
    {
        Scanner::assertType($tokens, TokenType::STRING);
        $propertyNameToken = $tokens->current();

        Scanner::skipOne($tokens);

        Scanner::assertType($tokens, TokenType::COLON);
        Scanner::skipOne($tokens);

        Scanner::skipSpace($tokens);

        $typeReferenceNode = $this->typeReferenceParser->parse($tokens);

        return new PropertyDeclarationNode(
            rangeInSource: Range::from(
                $propertyNameToken->boundaries->start,
                $typeReferenceNode->rangeInSource->end
            ),
            name: new PropertyNameNode(
                rangeInSource: $propertyNameToken->boundaries,
                value: PropertyName::from($propertyNameToken->value)
            ),
            type: $typeReferenceNode
        );
    }
}
