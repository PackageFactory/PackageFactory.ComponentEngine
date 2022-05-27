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

namespace PackageFactory\ComponentEngine\Definition;

use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

enum AccessType: string
{
    case MANDATORY = 'MANDATORY';
    case OPTIONAL = 'OPTIONAL';

    public static function fromTokenType(TokenType $tokenType): self
    {
        return match ($tokenType) {
            TokenType::PERIOD => self::MANDATORY,
            TokenType::OPTCHAIN => self::OPTIONAL,

            default => throw new \Exception('@TODO: Unknown AccessType')
        };
    }

    public function toTokenType(): TokenType
    {
        return match ($this) {
            self::MANDATORY => TokenType::PERIOD,
            self::OPTIONAL => TokenType::OPTCHAIN
        };
    }

    public function toPrecedence(): Precedence
    {
        return Precedence::ACCESS;
    }
}
