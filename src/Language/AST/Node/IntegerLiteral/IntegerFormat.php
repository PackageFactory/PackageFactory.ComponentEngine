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

namespace PackageFactory\ComponentEngine\Language\AST\Node\IntegerLiteral;

use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

enum IntegerFormat: string
{
    case BINARY = 'BINARY';
    case OCTAL = 'OCTAL';
    case DECIMAL = 'DECIMAL';
    case HEXADECIMAL = 'HEXADECIMAL';

    public static function fromTokenType(TokenType $tokenType): self
    {
        return match ($tokenType) {
            TokenType::NUMBER_BINARY => self::BINARY,
            TokenType::NUMBER_OCTAL => self::OCTAL,
            TokenType::NUMBER_DECIMAL => self::DECIMAL,
            TokenType::NUMBER_HEXADECIMAL => self::HEXADECIMAL,

            default => throw new \Exception('@TODO: Unknown Integer Format: ' . $tokenType->value)
        };
    }
}