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

namespace PackageFactory\ComponentEngine\Language\Parser\Import;

use PackageFactory\ComponentEngine\Domain\VariableName\VariableName;
use PackageFactory\ComponentEngine\Framework\PHP\Singleton\Singleton;
use PackageFactory\ComponentEngine\Language\AST\Node\Import\ImportedNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Import\ImportedNameNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Import\ImportNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Import\InvalidImportedNameNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\StringLiteral\StringLiteralNode;
use PackageFactory\ComponentEngine\Language\Lexer\Lexer;
use PackageFactory\ComponentEngine\Language\Lexer\LexerException;
use PackageFactory\ComponentEngine\Language\Lexer\Rule\Rule;
use PackageFactory\ComponentEngine\Language\Parser\StringLiteral\StringLiteralParser;
use PackageFactory\ComponentEngine\Parser\Source\Range;

final class ImportParser
{
    use Singleton;

    private ?StringLiteralParser $pathParser = null;

    public function parse(Lexer $lexer): ImportNode
    {
        try {
            $lexer->read(Rule::KEYWORD_FROM);
            $start = $lexer->buffer->getStart();
            $lexer->skipSpace();

            $path = $this->parsePath($lexer);

            $lexer->read(Rule::KEYWORD_IMPORT);
            $lexer->skipSpace();

            $names = $this->parseNames($lexer);
            $end = $lexer->buffer->getEnd();

            return new ImportNode(
                rangeInSource: Range::from($start, $end),
                path: $path,
                names: $names
            );
        } catch (LexerException $e) {
            throw ImportCouldNotBeParsed::becauseOfLexerException($e);
        }
    }

    private function parsePath(Lexer $lexer): StringLiteralNode
    {
        $this->pathParser ??= StringLiteralParser::singleton();

        $path = $this->pathParser->parse($lexer);
        $lexer->skipSpace();

        return $path;
    }

    private function parseNames(Lexer $lexer): ImportedNameNodes
    {
        $lexer->read(Rule::BRACKET_CURLY_OPEN);
        $start = $lexer->buffer->getStart();
        $lexer->skipSpaceAndComments();

        $nameNodes = [];
        while (!$lexer->peek(Rule::BRACKET_CURLY_CLOSE)) {
            $lexer->read(Rule::WORD);
            $nameNodes[] = new ImportedNameNode(
                rangeInSource: $lexer->buffer->getRange(),
                value: VariableName::from($lexer->buffer->getContents())
            );

            $lexer->skipSpaceAndComments();
            if ($lexer->probe(Rule::SYMBOL_COMMA)) {
                $lexer->skipSpaceAndComments();
            } else {
                break;
            }
        }

        $lexer->read(Rule::BRACKET_CURLY_CLOSE);
        $end = $lexer->buffer->getEnd();

        try {
            return new ImportedNameNodes(...$nameNodes);
        }  catch (InvalidImportedNameNodes $e) {
            throw ImportCouldNotBeParsed::becauseOfInvalidImportedNameNodes(
                cause: $e,
                affectedRangeInSource: $e->affectedRangeInSource ?? Range::from($start, $end)
            );
        }
    }

    public function parseName(Lexer $lexer): ImportedNameNode
    {
        $lexer->read(Rule::WORD);

        return new ImportedNameNode(
            rangeInSource: $lexer->buffer->getRange(),
            value: VariableName::from($lexer->buffer->getContents())
        );
    }
}
