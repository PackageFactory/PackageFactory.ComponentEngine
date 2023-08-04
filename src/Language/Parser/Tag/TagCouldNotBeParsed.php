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

namespace PackageFactory\ComponentEngine\Language\Parser\Tag;

use PackageFactory\ComponentEngine\Domain\TagName\TagName;
use PackageFactory\ComponentEngine\Language\Parser\ParserException;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenTypes;

final class TagCouldNotBeParsed extends ParserException
{
    public static function becauseOfClosingTagNameMismatch(
        TagName $expectedTagName,
        string $actualTagName,
        Range $affectedRangeInSource
    ): self {
        return new self(
            code: 1690976372,
            message: sprintf(
                'Tag could not be parsed, because the closing tag name "%s" did not match the opening tag name "%s".',
                $actualTagName,
                $expectedTagName->value
            ),
            affectedRangeInSource: $affectedRangeInSource
        );
    }

    public static function becauseOfUnexpectedToken(
        TokenTypes $expectedTokenTypes,
        Token $actualToken
    ): self {
        return new self(
            code: 1691156112,
            message: sprintf(
                'Tag could not be parsed because of unexpected token %s. '
                . 'Expected %s instead.',
                $actualToken->toDebugString(),
                $expectedTokenTypes->toDebugString()
            ),
            affectedRangeInSource: $actualToken->boundaries
        );
    }
}
