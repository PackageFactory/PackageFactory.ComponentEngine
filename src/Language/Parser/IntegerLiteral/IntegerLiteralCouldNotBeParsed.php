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

namespace PackageFactory\ComponentEngine\Language\Parser\IntegerLiteral;

use PackageFactory\ComponentEngine\Language\Parser\ParserException;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenTypes;

final class IntegerLiteralCouldNotBeParsed extends ParserException
{
    public static function becauseOfUnexpectedEndOfFile(): self
    {
        return new self(
            code: 1691238474,
            message: 'Integer literal could not be parsed because of unexpected end of file.'
        );
    }

    public static function becauseOfUnexpectedToken(
        TokenTypes $expectedTokenTypes,
        Token $actualToken
    ): self {
        return new self(
            code: 1691238491,
            message: sprintf(
                'Integer literal could not be parsed because of unexpected token %s. '
                . 'Expected %s instead.',
                $actualToken->toDebugString(),
                $expectedTokenTypes->toDebugString()
            ),
            affectedRangeInSource: $actualToken->boundaries
        );
    }
}
