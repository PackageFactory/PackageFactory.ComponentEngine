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

final class Comment
{
    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenize(SourceIterator $iterator): \Iterator
    {
        /** @var Fragment|null $capture */
        $capture = null;

        $delimiter = null;
        if ($lookAhead = $iterator->lookAhead(2)) {
            if ($lookAhead->value === '//') {
                yield Token::fromFragment(
                    TokenType::COMMENT_START,
                    $lookAhead
                );
                $delimiter = PHP_EOL;
                $iterator->skip(2);
            } elseif ($lookAhead->value === '/*') {
                yield Token::fromFragment(
                    TokenType::COMMENT_START,
                    $lookAhead
                );
                $delimiter = '*/';
                $iterator->skip(2);
            }
        }

        while ($iterator->valid()) {
            $fragment = $iterator->current();
            $value = $fragment->value;

            if ($delimiter && $value === $delimiter) {
                break;
            } elseif ($delimiter && $value === $delimiter[0]) {
                if ($lookAhead = $iterator->lookAhead(2)) {
                    if ($lookAhead->value === $delimiter) {
                        if ($capture !== null) {
                            yield Token::fromFragment(
                                TokenType::COMMENT_CONTENT,
                                $capture
                            );

                            $capture = null;
                        }

                        yield Token::fromFragment(
                            TokenType::COMMENT_END,
                            $lookAhead
                        );
                        $iterator->skip(2);
                        break;
                    }
                }
            } elseif ($capture === null) {
                $capture = $fragment;
                $iterator->next();
            } else {
                $capture = $capture->append($fragment);
                $iterator->next();
            }
        }

        if ($capture !== null) {
            yield Token::fromFragment(
                TokenType::COMMENT_CONTENT,
                $capture
            );

            $capture = null;
        }
    }
}
