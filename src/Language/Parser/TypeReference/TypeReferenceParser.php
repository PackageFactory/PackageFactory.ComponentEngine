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
use PackageFactory\ComponentEngine\Framework\PHP\Singleton\Singleton;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\InvalidTypeNameNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\InvalidTypeReferenceNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeNameNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeReferenceNode;
use PackageFactory\ComponentEngine\Language\Lexer\Lexer;
use PackageFactory\ComponentEngine\Language\Lexer\Rule\Rule;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Range;

final class TypeReferenceParser
{
    use Singleton;

    private ?Position $start = null;

    public function parse(Lexer $lexer): TypeReferenceNode
    {
        $this->start = null;
        if ($isOptional = (bool) $lexer->probe(Rule::SYMBOL_QUESTIONMARK)) {
            $this->start = $lexer->buffer->getStart();
        }
        $typeNameNodes = $this->parseTypeNames($lexer);
        $isArray = $this->parseIsArray($lexer);
        $end = $lexer->buffer->getEnd();

        assert($this->start !== null);

        try {
            return new TypeReferenceNode(
                rangeInSource: Range::from($this->start, $end),
                names: $typeNameNodes,
                isArray: $isArray,
                isOptional: $isOptional
            );
        } catch (InvalidTypeReferenceNode $e) {
            throw TypeReferenceCouldNotBeParsed::becauseOfInvalidTypeReferenceNode($e);
        }
    }

    public function parseTypeNames(Lexer $lexer): TypeNameNodes
    {
        $items = [];
        while (true) {
            $items[] = $this->parseTypeName($lexer);

            if ($lexer->isEnd() || !$lexer->probe(Rule::SYMBOL_PIPE)) {
                break;
            }
        }

        try {
            return new TypeNameNodes(...$items);
        } catch (InvalidTypeNameNodes $e) {
            throw TypeReferenceCouldNotBeParsed::becauseOfInvalidTypeTypeNameNodes($e);
        }
    }

    public function parseTypeName(Lexer $lexer): TypeNameNode
    {
        $lexer->read(Rule::WORD);
        $this->start ??= $lexer->buffer->getStart();

        return new TypeNameNode(
            rangeInSource: $lexer->buffer->getRange(),
            value: TypeName::from($lexer->buffer->getContents())
        );
    }

    public function parseIsArray(Lexer $lexer): bool
    {
        if ($lexer->isEnd()) {
            return false;
        }

        if ($lexer->probe(Rule::BRACKET_SQUARE_OPEN)) {
            $lexer->read(Rule::BRACKET_SQUARE_CLOSE);
            return true;
        }

        return false;
    }
}
