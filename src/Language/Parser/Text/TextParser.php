<?php

/**
 * PackageFactory.ComponentEngine - Universal View Components for PHP
 *   Copyright (C) 2023 Contributors of PackageFactory.ComponentEngine
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

namespace PackageFactory\ComponentEngine\Language\Parser\Text;

use PackageFactory\ComponentEngine\Framework\PHP\Singleton\Singleton;
use PackageFactory\ComponentEngine\Language\AST\Node\Text\TextNode;
use PackageFactory\ComponentEngine\Language\Lexer\Lexer;
use PackageFactory\ComponentEngine\Language\Lexer\Token\TokenType;
use PackageFactory\ComponentEngine\Language\Lexer\Token\TokenTypes;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Range;

final class TextParser
{
    use Singleton;

    private static TokenTypes $TOKEN_TYPES_END_DELIMITERS;
    private static TokenTypes $TOKEN_TYPES_CONTENT;

    private function __construct()
    {
        self::$TOKEN_TYPES_END_DELIMITERS = TokenTypes::from(
            TokenType::SYMBOL_CLOSE_TAG,
            TokenType::BRACKET_ANGLE_OPEN,
            TokenType::BRACKET_CURLY_OPEN
        );
        self::$TOKEN_TYPES_CONTENT = TokenTypes::from(
            TokenType::SPACE,
            TokenType::END_OF_LINE,
            TokenType::TEXT
        );
    }

    public function parse(Lexer $lexer, bool $preserveLeadingSpace = false): ?TextNode
    {
        /** @var null|Position $start */
        $start = null;
        $hasLeadingSpace = false;

        if ($lexer->probe(TokenType::SPACE)) {
            $start = $lexer->getStartPosition();
            $hasLeadingSpace = true;
        }

        if ($lexer->probe(TokenType::END_OF_LINE)) {
            $start ??= $lexer->getStartPosition();
            $hasLeadingSpace = false;
        }

        $lexer->skipSpace();
        if ($lexer->isEnd() || $lexer->peekOneOf(self::$TOKEN_TYPES_END_DELIMITERS)) {
            return null;
        }

        $hasTrailingSpace = false;
        $trailingSpaceContainsLineBreaks = false;
        $value = $hasLeadingSpace && $preserveLeadingSpace ? ' ' : '';
        while (!$lexer->isEnd() && !$lexer->peekOneOf(self::$TOKEN_TYPES_END_DELIMITERS)) {
            $lexer->readOneOf(self::$TOKEN_TYPES_CONTENT);

            if ($lexer->getTokenTypeUnderCursor() === TokenType::TEXT) {
                $start ??= $lexer->getStartPosition();
                if ($hasTrailingSpace) {
                    $value .= ' ';
                    $hasTrailingSpace = false;
                    $trailingSpaceContainsLineBreaks = false;
                }
                $value .= $lexer->getBuffer();
                continue;
            }

            if ($lexer->getTokenTypeUnderCursor() === TokenType::END_OF_LINE) {
                $trailingSpaceContainsLineBreaks = true;
            }

            $hasTrailingSpace = true;
        }

        if ($start === null) {
            return null;
        }

        $end = $lexer->getEndPosition();

        if ($hasTrailingSpace && !$trailingSpaceContainsLineBreaks && !$lexer->isEnd() && !$lexer->peek(TokenType::SYMBOL_CLOSE_TAG)) {
            $value .= ' ';
        }

        return new TextNode(
            rangeInSource: Range::from($start, $end),
            value: $value
        );
    }
}
