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

namespace PackageFactory\ComponentEngine\Parser\Ast\Afx;

use PackageFactory\ComponentEngine\Exception\ParserFailed;
use PackageFactory\ComponentEngine\Parser\Ast\Child;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;

final class Content implements Child, \JsonSerializable
{
    private function __construct(public readonly string $value)
    {
    }

    public static function fromTokenStream(TokenStream $stream): self
    {
        $value = '';
        $whitespace = false;
        while ($stream->valid()) {
            switch ($stream->current()->type) {
                case TokenType::AFX_TAG_CONTENT:
                    if ($whitespace) {
                        $value .= ' ';
                    }
                    $whitespace = false;
                    $value .= $stream->current()->value;
                    $stream->next();
                    break;
                case TokenType::WHITESPACE:
                case TokenType::END_OF_LINE:
                    if (!$whitespace) {
                        $whitespace = true;
                    }
                    $stream->next();
                    break;
                case TokenType::AFX_EXPRESSION_START:
                    if ($whitespace && !empty($value)) {
                        $value .= ' ';
                    }
                    break 2;
                case TokenType::AFX_TAG_START:
                    break 2;

                default:
                    throw ParserFailed::becauseOfUnexpectedToken(
                        $stream->current(),
                        [
                            TokenType::AFX_TAG_CONTENT,
                            TokenType::WHITESPACE,
                            TokenType::END_OF_LINE,
                            TokenType::AFX_EXPRESSION_START,
                            TokenType::AFX_TAG_START
                        ]
                    );
            }
        }

        return new self(value: $value);
    }

    public function jsonSerialize(): mixed
    {
        return $this->value;
    }
}
