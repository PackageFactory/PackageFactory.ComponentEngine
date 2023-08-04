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

namespace PackageFactory\ComponentEngine\Language\Parser\Expression;

use PackageFactory\ComponentEngine\Language\Parser\ParserException;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenTypes;

final class ExpressionCouldNotBeParsed extends ParserException
{
    public static function becauseOfUnexpectedToken(
        TokenTypes $expectedTokenTypes,
        Token $actualToken
    ): self {
        return new self(
            code: 1691063089,
            message: sprintf(
                'Expression could not be parsed because of unexpected token %s. '
                . 'Expected %s instead.',
                $actualToken->toDebugString(),
                $expectedTokenTypes->toDebugString()
            ),
            affectedRangeInSource: $actualToken->boundaries
        );
    }

    public static function becauseOfUnexpectedExceedingToken(
        Token $exceedingToken
    ): self {
        return new self(
            code: 1691141293,
            message: sprintf(
                'Expression could not be parsed because token stream was expected to end, '
                . 'but continued with %s instead.',
                $exceedingToken->toDebugString()
            ),
            affectedRangeInSource: $exceedingToken->boundaries
        );
    }
}
