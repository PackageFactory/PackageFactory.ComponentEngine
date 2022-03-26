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

namespace PackageFactory\ComponentEngine\Parser\Lexer\Scope;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

final class Identifier
{
    /**
     * @param string $char
     * @return boolean
     */
    public static function is(string $char): bool
    {
        return ctype_alnum($char) || $char === '_' || $char === '$';
    }

    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenize(SourceIterator $iterator): \Iterator
    {
        $capture = null;
        while ($iterator->valid()) {
            $value = $iterator->current()->value;

            if (self::is($value)) {
                if ($capture === null) {
                    $capture = $iterator->current();
                } else {
                    $capture = $capture->append($iterator->current());
                }
            } else {
                break;
            }

            $iterator->next();
        }

        if ($capture !== null) {
            yield Token::fromFragment(
                TokenType::IDENTIFIER,
                $capture
            );
        }
    }
}
