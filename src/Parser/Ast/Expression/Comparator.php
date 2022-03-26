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

namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Exception\ParserFailed;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;

enum Comparator implements \JsonSerializable
{
    case EQ;
    case NEQ;
    case GT;
    case GTE;
    case LT;
    case LTE;

    public static function fromToken(Token $token): self
    {
        return match ($token->value) {
            '===' => self::EQ,
            '!==' => self::NEQ,
            '>' => self::GT,
            '>=' => self::GTE,
            '<' => self::LT,
            '<=' => self::LTE,
            default => throw ParserFailed::becauseOfUnknownComparator($token),
        };
    }

    public function jsonSerialize(): mixed
    {
        return match ($this) {
            self::EQ => '===',
            self::NEQ => '!==',
            self::GT => '>',
            self::GTE => '>=',
            self::LT => '<',
            self::LTE => '<=',
        };
    }
}
