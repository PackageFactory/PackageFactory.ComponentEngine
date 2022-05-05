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

enum CharacterType
{
    case BRACKET_OPEN;
    case BRACKET_CLOSE;
    case ANGLE_OPEN;
    case ANGLE_CLOSE;
    case STRING_DELIMITER;
    case TEMPLATE_LITERAL_DELIMITER;
    case COMMENT_DELIMITER;
    case ESCAPE;
    case FORWARD_SLASH;
    case PERIOD;
    case SYMBOL;
    case DIGIT;
    case SPACE;
    case OTHER;

    public static function get(string $character): self
    {
        return match ($character) {
            '(', '[', '{' => self::BRACKET_OPEN,
            ')', ']', '}' => self::BRACKET_CLOSE,
            '<' => self::ANGLE_OPEN,
            '>' => self::ANGLE_CLOSE,
            '\'', '"' => self::STRING_DELIMITER,
            '`' => self::TEMPLATE_LITERAL_DELIMITER,
            '#' => self::COMMENT_DELIMITER,
            '\\' => self::ESCAPE,
            '/' => self::FORWARD_SLASH,
            '.' => self::PERIOD,
            '!', '%', '&', '|', '=', '?', ':', '-', ',', '+', '*', '$' => self::SYMBOL,
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' => self::DIGIT,
            default => match (true) {
                ctype_space($character) => self::SPACE,
                default => self::OTHER
            }
        };
    }

    public function is(string $character): bool
    {
        return self::get($character) === $this;
    }
}
