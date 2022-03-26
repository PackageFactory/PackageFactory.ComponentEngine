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

namespace PackageFactory\ComponentEngine\Parser\Lexer;

use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

/**
 * @implements \IteratorAggregate<mixed, Token>
 */
final class Tokenizer implements \IteratorAggregate
{
    /**
     * @param Source $source
     * @param class-string<Scope\Afx|Scope\Comment|Scope\Expression|Scope\Identifier|Scope\Module|Scope\Number|Scope\StringLiteral|Scope\TemplateLiteral|Scope\Whitespace> $rootScope
     */
    private function __construct(
        private readonly Source $source,
        private readonly string $rootScope
    ) {
    }

    /**
     * @param Source $source
     * @param class-string<Scope\Afx|Scope\Comment|Scope\Expression|Scope\Identifier|Scope\Module|Scope\Number|Scope\StringLiteral|Scope\TemplateLiteral|Scope\Whitespace> $rootScope
     * @return Tokenizer
     */
    public static function fromSource(
        Source $source,
        string $rootScope = Scope\Module::class
    ): Tokenizer {
        return new Tokenizer(
            source: $source,
            rootScope: $rootScope
        );
    }

    /**
     * @return \Iterator<mixed, Token>
     */
    public function getIterator(): \Iterator
    {
        $sourceIterator = SourceIterator::fromSource($this->source);
        yield from $this->rootScope::tokenize($sourceIterator);
    }
}
