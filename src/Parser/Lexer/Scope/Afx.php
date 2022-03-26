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

use PackageFactory\ComponentEngine\Parser\Lexer\Capture;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

final class Afx
{
    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenize(SourceIterator $iterator): \Iterator
    {
        $tags = 0;
        while ($iterator->valid()) {
            yield from Whitespace::tokenize($iterator);

            $value = $iterator->current()->value;

            if ($value === '<') {
                yield Token::fromFragment(
                    TokenType::AFX_TAG_START,
                    $iterator->current()
                );
                $iterator->next();
                if (!$iterator->willBe('/')) {
                    $tags++;
                }
            } elseif ($value === '/') {
                yield Token::fromFragment(
                    TokenType::AFX_TAG_CLOSE,
                    $iterator->current()
                );
                $iterator->next();
                $tags--;
            } elseif ($value === '>') {
                yield Token::fromFragment(
                    TokenType::AFX_TAG_END,
                    $iterator->current()
                );
                $iterator->next();

                if ($tags === 0) {
                    return;
                }

                yield from Whitespace::tokenize($iterator);

                if (!$iterator->willBe('<')) {
                    yield from self::tokenizeContent($iterator);
                }
            } elseif ($value === '=') {
                yield Token::fromFragment(
                    TokenType::AFX_ATTRIBUTE_ASSIGNMENT,
                    $iterator->current()
                );
                $iterator->next();
            } elseif ($value === '"') {
                yield from StringLiteral::tokenize($iterator);
            } elseif ($value === '{') {
                yield Token::fromFragment(
                    TokenType::AFX_EXPRESSION_START,
                    $iterator->current()
                );
                $iterator->next();

                yield from Expression::tokenize($iterator, ['}']);
            } elseif ($value === '}') {
                yield Token::fromFragment(
                    TokenType::AFX_EXPRESSION_END,
                    $iterator->current()
                );
                $iterator->next();
            } elseif (Identifier::is($value)) {
                yield from Identifier::tokenize($iterator);
            } else {
                break;
            }
        }
    }

    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenizeContent(SourceIterator $iterator): \Iterator
    {
        $capture = Capture::createEmpty();

        while ($iterator->valid()) {
            $value = $iterator->current()->value;

            if ($value === '<') {
                break;
            } elseif (ctype_space($value)) {
                yield from $capture->flush(TokenType::AFX_TAG_CONTENT);
                yield from Whitespace::tokenize($iterator);
            } elseif ($value === '{') {
                yield from $capture->flush(TokenType::AFX_TAG_CONTENT);
                yield Token::fromFragment(
                    TokenType::AFX_EXPRESSION_START,
                    $iterator->current()
                );
                $iterator->next();

                yield from Expression::tokenize($iterator, ['}']);
            } elseif ($value === '}') {
                yield from $capture->flush(TokenType::AFX_TAG_CONTENT);
                yield Token::fromFragment(
                    TokenType::AFX_EXPRESSION_END,
                    $iterator->current()
                );
                $iterator->next();
            } else {
                $capture->append($iterator->current());
                $iterator->next();
            }
        }

        yield from $capture->flush(TokenType::AFX_TAG_CONTENT);
    }
}
