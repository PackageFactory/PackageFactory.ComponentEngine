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

namespace PackageFactory\ComponentEngine\Language\Parser\TypeReference;

use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\InvalidTypeReferenceNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeReferenceNode;
use PackageFactory\ComponentEngine\Language\AST\NodeAttributes\NodeAttributes;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class TypeReferenceParser
{
    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return TypeReferenceNode
     */
    public function parse(\Iterator $tokens): TypeReferenceNode
    {
        $isOptional = false;
        if (Scanner::type($tokens) === TokenType::QUESTIONMARK) {
            $startingToken = $tokens->current();
            $isOptional = true;
            Scanner::skipOne($tokens);
        }

        Scanner::assertType($tokens, TokenType::STRING);

        $typeNameToken = $finalToken = $tokens->current();
        $startingToken = $startingToken ?? $typeNameToken;

        Scanner::skipOne($tokens);

        $isArray = false;
        if (!Scanner::isEnd($tokens) && Scanner::type($tokens) === TokenType::BRACKET_SQUARE_OPEN) {
            Scanner::skipOne($tokens);
            Scanner::assertType($tokens, TokenType::BRACKET_SQUARE_CLOSE);

            $finalToken = $tokens->current();
            $isArray = true;

            Scanner::skipOne($tokens);
        }

        try {
            return new TypeReferenceNode(
                attributes: new NodeAttributes(
                    pathToSource: $startingToken->sourcePath,
                    rangeInSource: Range::from(
                        $startingToken->boundaries->start,
                        $finalToken->boundaries->end
                    )
                ),
                name: TypeName::from($typeNameToken->value),
                isArray: $isArray,
                isOptional: $isOptional
            );
        } catch (InvalidTypeReferenceNode $e) {
            throw TypeReferenceCouldNotBeParsed::becauseOfInvalidTypeReferenceNode(
                cause: $e,
                affectedToken: $startingToken
            );
        }
    }
}
