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

use PackageFactory\ComponentEngine\Exception\ParserFailed;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Source\Source;

final class Module implements \JsonSerializable
{
    /**
     * @param Source $source
     * @param array|Import[] $imports
     * @param array|Export[] $exports
     * @param array|Constant[] $constants
     */
    private function __construct(
        public readonly Source $source,
        public readonly array $imports,
        public readonly array $exports,
        public readonly array $constants
    ) {
    }

    public static function fromTokenStream(TokenStream $stream): self
    {
        $source = $stream->current()->source;

        $imports = [];
        $exports = [];
        $constants = [];
        while ($stream->valid()) {
            $stream->skipWhiteSpaceAndComments();

            switch ($stream->current()->type) {
                case TokenType::MODULE_KEYWORD_IMPORT:
                    foreach (Import::fromTokenStream($stream) as $import) {
                        $imports[(string) $import->domesticName] = $import;
                    }
                    break;
                case TokenType::MODULE_KEYWORD_EXPORT:
                    $export = Export::fromTokenStream($stream);
                    $exports[(string) $export->name] = $export;
                    break;
                case TokenType::MODULE_KEYWORD_CONST:
                    $constant = Constant::fromTokenStream($stream);
                    $constants[(string) $constant->name] = $constant;
                    break;
                default:
                    throw ParserFailed::becauseOfUnexpectedToken(
                        $stream->current(),
                        [
                            TokenType::MODULE_KEYWORD_IMPORT,
                            TokenType::MODULE_KEYWORD_EXPORT,
                            TokenType::MODULE_KEYWORD_CONST
                        ]
                    );
            }
        }

        return new self(
            source: $source,
            imports: $imports,
            exports: $exports,
            constants: $constants
        );
    }

    public function hasImport(string $name): bool
    {
        return isset($this->imports[$name]);
    }

    public function getImport(string $name): Import
    {
        if (isset($this->imports[$name])) {
            return $this->imports[$name];
        }

        throw new \Exception('@TODO: Import does not exist: ' . $name);
    }

    public function getExport(string $name): Export
    {
        if (isset($this->exports[$name])) {
            return $this->exports[$name];
        }

        throw new \Exception('@TODO: Export does not exist: ' . $name);
    }

    public function hasConstant(string $name): bool
    {
        return isset($this->constants[$name]);
    }

    public function getConstant(string $name): Constant
    {
        if (isset($this->constants[$name])) {
            return $this->constants[$name];
        }

        throw new \Exception('@TODO: Constant does not exist: ' . $name);
    }

    public function jsonSerialize(): mixed
    {
        throw new \Exception('@TODO: Module::jsonSerialize');
    }
}
