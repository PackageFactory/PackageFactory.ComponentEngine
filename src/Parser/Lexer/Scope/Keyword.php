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

use PackageFactory\ComponentEngine\Parser\Source\Fragment;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

final class Keyword
{
    /**
     * @param SourceIterator $iterator
     * @param string $keyword
     * @return null|Fragment
     */
    public static function extract(SourceIterator $iterator, string $keyword): ?Fragment
    {
        $value = $iterator->current()->value;

        if ($value === $keyword[0]) {
            $length = mb_strlen($keyword);

            if ($fragment = $iterator->lookAhead($length)) {
                if ($fragment->value === $keyword) {
                    if ($lookAhead = $iterator->lookAhead($length + 1)) {
                        $lookAheadValue = $lookAhead->value;
                        if (!Identifier::is(mb_substr($lookAheadValue, $length))) {
                            $iterator->skip($length);
                            return $fragment;
                        }
                    } else {
                        $iterator->skip($length);
                        return $fragment;
                    }
                }
            }
        }

        return null;
    }
}
