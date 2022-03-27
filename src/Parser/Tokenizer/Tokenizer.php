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

namespace PackageFactory\ComponentEngine\Parser\Tokenizer;

use PackageFactory\ComponentEngine\Parser\Source\Fragment;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

/**
 * @implements \IteratorAggregate<mixed, Token>
 */
final class Tokenizer implements \IteratorAggregate
{
    private function __construct(private readonly Source $source)
    {
    }

    public static function fromSource(Source $source): Tokenizer
    {
        return new Tokenizer(source: $source);
    }

    /**
     * @return \Iterator<mixed, Token>
     */
    public function getIterator(): \Iterator
    {
        $sourceIterator = SourceIterator::fromSource($this->source);

        /** @var ?Fragment $buffer */
        $buffer = null;
        // $captureMode = CaptureMode::DEFAULT;

        foreach ($sourceIterator as $fragment) {
            if ($buffer === null) {
                $buffer = $fragment;
            } else {
                $buffer = $buffer->append($fragment);
            }
        }

        if ($buffer !== null) {
            yield Token::fromFragment(TokenType::KEYWORD, $buffer);
        }
    }
}
