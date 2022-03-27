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

namespace PackageFactory\ComponentEngine\Parser\Tokenizer;

enum CaptureMode
{
    case DEFAULT;

    case COMMENT_SINGLE_LINE;
    case COMMENT_MULTI_LINE;

    case WHITESPACE;

    case STRING_SINGLE_QUOTE;
    case STRING_DOUBLE_QUOTE;

    case TEMPLATE_LITERAL;

    case AFX_TAG;
    case AFX_CONTENT;

    public function next(string $character): CaptureMode
    {
        return match ($this) {
            self::DEFAULT, self::WHITESPACE => match (true) {
                ctype_space($character) => self::WHITESPACE,
                default => self::DEFAULT
            },
            default => throw new \Exception("@TODO: Handle case " . $this->name),
        };
    }
}
