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

namespace PackageFactory\ComponentEngine\Language\Parser\Match;

use PackageFactory\ComponentEngine\Language\AST\Node\Match\InvalidMatchArmNodes;
use PackageFactory\ComponentEngine\Language\Parser\ParserException;
use PackageFactory\ComponentEngine\Parser\Source\Range;

final class MatchCouldNotBeParsed extends ParserException
{
    public static function becauseOfInvalidMatchArmNodes(
        InvalidMatchArmNodes $cause,
        ?Range $affectedRangeInSource = null
    ): self {
        return new self(
            code: 1691152175,
            message: sprintf(
                'Match could not be parsed because of invalid match arm nodes: %s.',
                $cause->getMessage()
            ),
            affectedRangeInSource: $cause->affectedRangeInSource ?? $affectedRangeInSource,
            cause: $cause
        );
    }
}
