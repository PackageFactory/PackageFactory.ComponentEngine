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
use PackageFactory\ComponentEngine\Language\Lexer\Token\Token;
use PackageFactory\ComponentEngine\Language\Lexer\Token\TokenType;
use PackageFactory\ComponentEngine\Language\Lexer\Token\TokenTypes;
use PackageFactory\ComponentEngine\Language\Parser\StringLiteral\StringLiteralParser;
use PackageFactory\ComponentEngine\Parser\Source\Range;

final class ImportParser
{
    use Singleton;

    private static TokenTypes $TOKEN_TYPES_NAME_BOUNDARIES;

    private ?StringLiteralParser $pathParser = null;

    private function __construct()
    {
        self::$TOKEN_TYPES_NAME_BOUNDARIES ??= TokenTypes::from(
            TokenType::WORD,
            TokenType::SYMBOL_COMMA,
            TokenType::BRACKET_CURLY_CLOSE
        );
    }

    public function parse(Lexer $lexer): ImportNode
    {
        try {
            $lexer->read(TokenType::KEYWORD_FROM);
            $start = $lexer->getStartPosition();
            $lexer->skipSpace();

            $path = $this->parsePath($lexer);

            $lexer->read(TokenType::KEYWORD_IMPORT);
            $lexer->skipSpace();

            $names = $this->parseNames($lexer);
            $end = $lexer->getEndPosition();

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
        $lexer->read(TokenType::BRACKET_CURLY_OPEN);
        $start = $lexer->getStartPosition();
        $lexer->skipSpaceAndComments();

        $nameTokens = [];
        while (!$lexer->peek(TokenType::BRACKET_CURLY_CLOSE)) {
            $lexer->read(TokenType::WORD);
            $nameTokens[] = $lexer->getTokenUnderCursor();

            $lexer->skipSpaceAndComments();
            if ($lexer->probe(TokenType::SYMBOL_COMMA)) {
                $lexer->skipSpaceAndComments();
            } else {
                break;
            }
        }

        $lexer->read(TokenType::BRACKET_CURLY_CLOSE);
        $end = $lexer->getEndPosition();

        try {
            return new ImportedNameNodes(
                ...array_map(
                    static fn (Token $nameToken) => new ImportedNameNode(
                        rangeInSource: $nameToken->rangeInSource,
                        value: VariableName::from($nameToken->value)
                    ),
                    $nameTokens
                )
            );
        }  catch (InvalidImportedNameNodes $e) {
            throw ImportCouldNotBeParsed::becauseOfInvalidImportedNameNodes(
                cause: $e,
                affectedRangeInSource: $e->affectedRangeInSource ?? Range::from($start, $end)
            );
        }
    }
}
