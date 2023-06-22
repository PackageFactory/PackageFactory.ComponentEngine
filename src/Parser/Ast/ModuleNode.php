<?php

/**
 * PackageFactory.ComponentEngine - Universal View Components for PHP
 *   Copyright (C) 2022 Contributors of PackageFactory.ComponentEngine
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

namespace PackageFactory\ComponentEngine\Parser\Ast;

use PackageFactory\ComponentEngine\Parser\Parser\Module\ModuleParser;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class ModuleNode implements \JsonSerializable
{
    public function __construct(
        public readonly ImportNodes $imports,
        public readonly ExportNodes $exports,
    ) {
    }

    public static function fromString(string $moduleAsString): self
    {
        return ModuleParser::parseFromString($moduleAsString);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return self
     */
    public static function fromTokens(\Iterator $tokens): self
    {
        $imports = ImportNodes::empty();
        $exports = ExportNodes::empty();

        while ($tokens->valid()) {
            Scanner::skipSpaceAndComments($tokens);
            if ($tokens->valid()) {
                switch ($tokens->current()->type) {
                    case TokenType::KEYWORD_FROM:
                        foreach (ImportNode::fromTokens($tokens) as $import) {
                            $imports = $imports->withAddedImport($import);
                        }
                        break;
                    case TokenType::KEYWORD_EXPORT:
                        $exports = $exports->withAddedExport(ExportNode::fromTokens($tokens));
                        break;
                    default:
                        Scanner::assertType($tokens, TokenType::KEYWORD_FROM, TokenType::KEYWORD_EXPORT);
                        break;
                }
            }
        }

        return new self(
            imports: $imports,
            exports: $exports
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => 'ModuleNode',
            'payload' => [
                'imports' => $this->imports,
                'exports' => $this->exports
            ]
        ];
    }
}
