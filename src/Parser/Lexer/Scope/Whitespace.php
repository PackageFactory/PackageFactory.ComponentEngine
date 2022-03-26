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
use PackageFactory\ComponentEngine\Parser\Source\Fragment;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

final class Whitespace
{
    /**
     * @param Fragment $fragment
     * @return boolean
     */
    public static function is(Fragment $fragment): bool
    {
        return ctype_space($fragment->value);
    }

    /**
     * @phpstan-impure
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenize(SourceIterator $iterator): \Iterator
    {
        /** @var Fragment|null $capture */
        $capture = null;

        while ($iterator->valid()) {
            if ($iterator->current()->value === PHP_EOL) {
                if ($capture !== null) {
                    yield Token::fromFragment(
                        TokenType::WHITESPACE,
                        $capture
                    );

                    $capture = null;
                }

                yield Token::fromFragment(
                    TokenType::END_OF_LINE,
                    $iterator->current()
                );
                $iterator->next();
            } elseif (self::is($iterator->current())) {
                if ($capture === null) {
                    $capture = $iterator->current();
                } else {
                    $capture = $capture->append($iterator->current());
                }

                $iterator->next();
            } else {
                if ($capture !== null) {
                    yield Token::fromFragment(
                        TokenType::WHITESPACE,
                        $capture
                    );

                    $capture = null;
                }
                break;
            }
        }

        if ($capture !== null) {
            yield Token::fromFragment(
                TokenType::WHITESPACE,
                $capture
            );

            $capture = null;
        }
    }
}
