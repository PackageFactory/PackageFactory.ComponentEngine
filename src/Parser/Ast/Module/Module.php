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

namespace PackageFactory\ComponentEngine\Parser\Ast\Module;

use PackageFactory\ComponentEngine\Parser\Ast\Module\Export\Exports;
use PackageFactory\ComponentEngine\Parser\Ast\Module\Export\ExportsBuilder;
use PackageFactory\ComponentEngine\Parser\Ast\Module\Import\Imports;
use PackageFactory\ComponentEngine\Parser\Ast\Module\Import\ImportsBuilder;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;

final class Module implements \JsonSerializable
{
    private function __construct(
        public readonly Imports $imports,
        public readonly Exports $exports,
    ) {
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return self
     */
    public static function fromTokens(\Iterator $tokens): self
    {
        $importsBuilder = new ImportsBuilder();
        $exportsBuilder = new ExportsBuilder();

        while ($tokens->valid()) {
            Scanner::skipSpaceAndComments($tokens);
            if ($tokens->valid()) {
                $builder = match ($tokens->current()->type) {
                    TokenType::KEYWORD_IMPORT => $importsBuilder,
                    TokenType::KEYWORD_EXPORT => $exportsBuilder,
                    default => throw new \Exception('@TODO: Unexpected Token ' . $tokens->current()->type->value)
                };

                $builder->addFromTokens($tokens);
            }
        }

        return new self(
            imports: $importsBuilder->build(),
            exports: $exportsBuilder->build()
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            "type" => "Module",
            "payload" => [
                "imports" => $this->imports,
                "exports" => $this->exports
            ]
        ];
    }
}
