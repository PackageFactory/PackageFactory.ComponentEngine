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

use PackageFactory\ComponentEngine\Language\AST\Node\Text\TextNode;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class TextParser
{
    /**
     * @param \Iterator<mixed,Token> $tokens
     * @param boolean $preserveLeadingSpace
     * @return null|TextNode
     */
    public function parse(\Iterator $tokens, bool $preserveLeadingSpace = false): ?TextNode
    {
        $value = '';
        $startingToken = null;
        $finalToken = null;
        $ignoreSpace = false;
        $keepTrailingSpace = false;
        $forceTrimTrailingSpace = false;
        while (!Scanner::isEnd($tokens)) {
            $startingToken ??= $tokens->current();
            switch (Scanner::type($tokens)) {
                case TokenType::BRACKET_CURLY_OPEN:
                case TokenType::TAG_START_OPENING:
                    $keepTrailingSpace = true;
                    break 2;
                case TokenType::TAG_START_CLOSING:
                    $value = rtrim($value);
                    break 2;
                case TokenType::SPACE:
                case TokenType::END_OF_LINE:
                    if (!$ignoreSpace) {
                        $value .= ' ';
                    }
                    $ignoreSpace = true;
                    if (Scanner::type($tokens) === TokenType::END_OF_LINE) {
                        $forceTrimTrailingSpace = true;
                    }
                    $finalToken = $tokens->current();
                    Scanner::skipOne($tokens);
                    break;
                default:
                    $value .= Scanner::value($tokens);
                    $ignoreSpace = false;
                    $forceTrimTrailingSpace = false;
                    $finalToken = $tokens->current();
                    Scanner::skipOne($tokens);
                    break;
            }
        }

        if (is_null($startingToken) || is_null($finalToken)) {
            return null;
        }

        if (!$preserveLeadingSpace) {
            $value = ltrim($value);
        }

        if (!$keepTrailingSpace || $forceTrimTrailingSpace) {
            $value = rtrim($value);
        }

        if ($value === '' || $value === ' ') {
            return null;
        }

        return new TextNode(
            rangeInSource: Range::from(
                $startingToken->boundaries->start,
                $finalToken->boundaries->end
            ),
            value: $value
        );
    }
}
