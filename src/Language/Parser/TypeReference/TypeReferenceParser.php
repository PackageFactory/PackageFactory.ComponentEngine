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
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\InvalidTypeNameNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\InvalidTypeReferenceNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeNameNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeReferenceNode;
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
    public function parse(\Iterator &$tokens): TypeReferenceNode
    {
        $startingToken = $tokens->current();
        $questionmarkToken = $this->extractQuestionmarkToken($tokens);
        $isOptional = !is_null($questionmarkToken);

        $typeNameNodes = $this->parseTypeNames($tokens);

        $closingArrayToken = $this->extractClosingArrayToken($tokens);
        $isArray = !is_null($closingArrayToken);

        $rangeInSource = Range::from(
            $startingToken->boundaries->start,
            $closingArrayToken?->boundaries->end
                ?? $typeNameNodes->getLast()->rangeInSource->end
        );

        try {
            return new TypeReferenceNode(
                rangeInSource: $rangeInSource,
                names: $typeNameNodes,
                isArray: $isArray,
                isOptional: $isOptional
            );
        } catch (InvalidTypeReferenceNode $e) {
            throw TypeReferenceCouldNotBeParsed::becauseOfInvalidTypeReferenceNode($e);
        }
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return Token
     */
    public function extractQuestionmarkToken(\Iterator &$tokens): ?Token
    {
        if (Scanner::type($tokens) === TokenType::QUESTIONMARK) {
            $questionmarkToken = $tokens->current();
            Scanner::skipOne($tokens);

            return $questionmarkToken;
        }

        return null;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return TypeNameNodes
     */
    public function parseTypeNames(\Iterator &$tokens): TypeNameNodes
    {
        $items = [];
        while (true) {
            $items[] = $this->parseTypeName($tokens);

            if (Scanner::isEnd($tokens) || Scanner::type($tokens) !== TokenType::PIPE) {
                break;
            }

            Scanner::skipOne($tokens);
        }

        try {
            return new TypeNameNodes(...$items);
        } catch (InvalidTypeNameNodes $e) {
            throw TypeReferenceCouldNotBeParsed::becauseOfInvalidTypeTypeNameNodes($e);
        }
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return TypeNameNode
     */
    public function parseTypeName(\Iterator &$tokens): TypeNameNode
    {
        Scanner::assertType($tokens, TokenType::STRING);

        $typeNameToken = $tokens->current();

        Scanner::skipOne($tokens);

        return new TypeNameNode(
            rangeInSource: $typeNameToken->boundaries,
            value: TypeName::from($typeNameToken->value)
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return Token
     */
    public function extractClosingArrayToken(\Iterator &$tokens): ?Token
    {
        if (!Scanner::isEnd($tokens) && Scanner::type($tokens) === TokenType::BRACKET_SQUARE_OPEN) {
            Scanner::skipOne($tokens);
            Scanner::assertType($tokens, TokenType::BRACKET_SQUARE_CLOSE);

            $closingArrayToken = $tokens->current();

            Scanner::skipOne($tokens);

            return $closingArrayToken;
        }

        return null;
    }
}
