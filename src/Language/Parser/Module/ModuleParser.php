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

namespace PackageFactory\ComponentEngine\Language\Parser\Module;

use PackageFactory\ComponentEngine\Framework\PHP\Singleton\Singleton;
use PackageFactory\ComponentEngine\Language\AST\Node\Export\ExportNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Import\ImportNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Import\ImportNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Module\ModuleNode;
use PackageFactory\ComponentEngine\Language\Lexer\Lexer;
use PackageFactory\ComponentEngine\Language\Lexer\LexerException;
use PackageFactory\ComponentEngine\Language\Lexer\Rule\Rule;
use PackageFactory\ComponentEngine\Language\Parser\Export\ExportParser;
use PackageFactory\ComponentEngine\Language\Parser\Import\ImportParser;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Range;

final class ModuleParser
{
    use Singleton;

    private ?ImportParser $importParser = null;
    private ?ExportParser $exportParser = null;

    public function parse(Lexer $lexer): ModuleNode
    {
        try {
            $lexer->skipSpaceAndComments();

            $imports = $this->parseImports($lexer);
            $export = $this->parseExport($lexer);

            $lexer->skipSpaceAndComments();
            $lexer->assertIsEnd();

            return new ModuleNode(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    $export->rangeInSource->end
                ),
                imports: $imports,
                export: $export
            );
        } catch (LexerException $e) {
            throw ModuleCouldNotBeParsed::becauseOfLexerException($e);
        }
    }

    private function parseImports(Lexer $lexer): ImportNodes
    {
        $items = [];
        while ($lexer->peek(Rule::KEYWORD_FROM)) {
            $items[] = $this->parseImport($lexer);
        }

        return new ImportNodes(...$items);
    }

    private function parseImport(Lexer $lexer): ImportNode
    {
        $this->importParser ??= ImportParser::singleton();

        $import = $this->importParser->parse($lexer);
        $lexer->skipSpaceAndComments();

        return $import;
    }

    private function parseExport(Lexer $lexer): ExportNode
    {
        $this->exportParser ??= ExportParser::singleton();

        $export = $this->exportParser->parse($lexer);

        return $export;
    }
}
