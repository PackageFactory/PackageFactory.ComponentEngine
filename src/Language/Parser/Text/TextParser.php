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
use PackageFactory\ComponentEngine\Language\Lexer\Rule\Rule;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Range;

final class TextParser
{
    use Singleton;

    private const RULES_END_DELIMITERS = [
        Rule::SYMBOL_CLOSE_TAG,
        Rule::BRACKET_ANGLE_OPEN,
        Rule::BRACKET_CURLY_OPEN
    ];
    private const RULES_CONTENT = [
        Rule::SPACE,
        Rule::END_OF_LINE,
        Rule::TEXT
    ];

    public function parse(Lexer $lexer, bool $preserveLeadingSpace = false): ?TextNode
    {
        /** @var null|Position $start */
        $start = null;
        $hasLeadingSpace = false;

        if ($lexer->probe(Rule::SPACE)) {
            $start = $lexer->buffer->getStart();
            $hasLeadingSpace = true;
        }

        if ($lexer->probe(Rule::END_OF_LINE)) {
            $start ??= $lexer->buffer->getStart();
            $hasLeadingSpace = false;
        }

        $lexer->skipSpace();
        if ($lexer->isEnd() || $lexer->peekOneOf(...self::RULES_END_DELIMITERS)) {
            return null;
        }

        $hasTrailingSpace = false;
        $trailingSpaceContainsLineBreaks = false;
        $value = $hasLeadingSpace && $preserveLeadingSpace ? ' ' : '';
        while (!$lexer->isEnd() && !$lexer->peekOneOf(...self::RULES_END_DELIMITERS)) {
            $rule = $lexer->readOneOf(...self::RULES_CONTENT);

            if ($rule === Rule::TEXT) {
                $start ??= $lexer->buffer->getStart();
                if ($hasTrailingSpace) {
                    $value .= ' ';
                    $hasTrailingSpace = false;
                    $trailingSpaceContainsLineBreaks = false;
                }
                $value .= $lexer->buffer->getContents();
                continue;
            }

            if ($rule === Rule::END_OF_LINE) {
                $trailingSpaceContainsLineBreaks = true;
            }

            $hasTrailingSpace = true;
        }

        if ($start === null) {
            return null;
        }

        $end = $lexer->buffer->getEnd();

        if ($hasTrailingSpace && !$trailingSpaceContainsLineBreaks && !$lexer->isEnd() && !$lexer->peek(Rule::SYMBOL_CLOSE_TAG)) {
            $value .= ' ';
        }

        return new TextNode(
            rangeInSource: Range::from($start, $end),
            value: $value
        );
    }
}
