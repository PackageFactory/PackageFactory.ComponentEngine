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

namespace PackageFactory\ComponentEngine\Language\Parser\Import;

use PackageFactory\ComponentEngine\Language\AST\Node\Import\InvalidImportedNameNodes;
use PackageFactory\ComponentEngine\Language\Parser\ParserException;
use PackageFactory\ComponentEngine\Parser\Source\Range;

final class ImportCouldNotBeParsed extends ParserException
{
    protected const TITLE = 'Import could not be parsed';

    public static function becauseOfInvalidImportedNameNodes(
        InvalidImportedNameNodes $cause,
        Range $affectedRangeInSource
    ): self {
        return new self(
            code: 1691181627,
            message: $cause->getMessage(),
            affectedRangeInSource: $cause->affectedRangeInSource ?? $affectedRangeInSource
        );
    }
}
