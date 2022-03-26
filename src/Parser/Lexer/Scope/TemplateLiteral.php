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

final class TemplateLiteral
{
    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenize(SourceIterator $iterator): \Iterator
    {
        /** @var Fragment|null $capture */
        $capture = null;
        $interpolation = false;
        $delimiter = $iterator->current();

        if ($delimiter->value === '`') {
            yield Token::fromFragment(
                TokenType::TEMPLATE_LITERAL_START,
                $delimiter
            );
            $iterator->next();
        } else {
            $delimiter = null;
        }

        while ($iterator->valid()) {
            $fragment = $iterator->current();
            $value = $fragment->value;

            if ($value === '\\') {
                if ($capture !== null) {
                    yield Token::fromFragment(
                        TokenType::TEMPLATE_LITERAL_CONTENT,
                        $capture
                    );

                    $capture = null;
                }

                yield Token::fromFragment(
                    TokenType::TEMPLATE_LITERAL_ESCAPE,
                    $fragment
                );

                $iterator->next();

                if ($iterator->valid()) {
                    yield Token::fromFragment(
                        TokenType::TEMPLATE_LITERAL_ESCAPED_CHARACTER,
                        $iterator->current()
                    );

                    $iterator->next();
                }
            } elseif ($value === PHP_EOL) {
                if ($capture !== null) {
                    yield Token::fromFragment(
                        TokenType::TEMPLATE_LITERAL_CONTENT,
                        $capture
                    );

                    $capture = null;
                }

                yield Token::fromFragment(
                    TokenType::END_OF_LINE,
                    $fragment
                );

                $iterator->next();
            } elseif ($value === '$') {
                if ($lookAhead = $iterator->willBe('${')) {
                    if ($capture !== null) {
                        yield Token::fromFragment(
                            TokenType::TEMPLATE_LITERAL_CONTENT,
                            $capture
                        );

                        $capture = null;
                    }
                    $interpolation = true;
                    yield Token::fromFragment(
                        TokenType::TEMPLATE_LITERAL_INTERPOLATION_START,
                        $lookAhead
                    );
                    $iterator->skip(2);

                    yield from Expression::tokenize($iterator, ['}']);
                } elseif ($capture === null) {
                    $capture = $fragment;
                    $iterator->next();
                } else {
                    $capture = $capture->append($fragment);
                    $iterator->next();
                }
            } elseif ($value === '}' && $interpolation) {
                yield Token::fromFragment(
                    TokenType::TEMPLATE_LITERAL_INTERPOLATION_END,
                    $iterator->current()
                );

                $capture = null;
                $iterator->next();
            } elseif ($delimiter !== null && $value === $delimiter->value) {
                if ($capture !== null) {
                    yield Token::fromFragment(
                        TokenType::TEMPLATE_LITERAL_CONTENT,
                        $capture
                    );

                    $capture = null;
                }

                yield Token::fromFragment(
                    TokenType::TEMPLATE_LITERAL_END,
                    $fragment
                );

                $iterator->next();
                break;
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
                TokenType::TEMPLATE_LITERAL_CONTENT,
                $capture
            );

            $capture = null;
        }
    }
}
