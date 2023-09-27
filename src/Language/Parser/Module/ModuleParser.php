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
use PackageFactory\ComponentEngine\Language\Parser\Export\ExportParser;
use PackageFactory\ComponentEngine\Language\Parser\Import\ImportParser;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class ModuleParser
{
    use Singleton;

    private ?ImportParser $importParser = null;
    private ?ExportParser $exportParser = null;

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ModuleNode
     */
    public function parse(\Iterator &$tokens): ModuleNode
    {
        Scanner::skipSpaceAndComments($tokens);

        $imports = $this->parseImports($tokens);
        $export = $this->parseExport($tokens);

        if (!Scanner::isEnd($tokens)) {
            throw ModuleCouldNotBeParsed::becauseOfUnexpectedExceedingToken(
                exceedingToken: $tokens->current()
            );
        }

        return new ModuleNode(
            rangeInSource: Range::from(
                new Position(0, 0),
                $export->rangeInSource->end
            ),
            imports: $imports,
            export: $export
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ImportNodes
     */
    private function parseImports(\Iterator &$tokens): ImportNodes
    {
        $items = [];
        while (Scanner::type($tokens) !== TokenType::KEYWORD_EXPORT) {
            $items[] = $this->parseImport($tokens);
        }

        return new ImportNodes(...$items);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ImportNode
     */
    private function parseImport(\Iterator &$tokens): ImportNode
    {
        $this->importParser ??= ImportParser::singleton();

        $import = $this->importParser->parse($tokens);
        Scanner::skipSpaceAndComments($tokens);

        return $import;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ExportNode
     */
    private function parseExport(\Iterator &$tokens): ExportNode
    {
        $this->exportParser ??= ExportParser::singleton();

        $export = $this->exportParser->parse($tokens);
        Scanner::skipSpaceAndComments($tokens);

        return $export;
    }
}
