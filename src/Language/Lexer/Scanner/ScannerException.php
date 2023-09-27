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

namespace PackageFactory\ComponentEngine\Language\Lexer\Scanner;

use PackageFactory\ComponentEngine\Language\Lexer\Buffer\Buffer;
use PackageFactory\ComponentEngine\Parser\Source\Range;

final class ScannerException extends \Exception
{
    private function __construct(
        int $code,
        string $message,
        public readonly Range $affectedRangeInSource
    ) {
        $message = sprintf(
            '[%s:%s] %s',
            $affectedRangeInSource->start->lineNumber,
            $affectedRangeInSource->start->columnNumber,
            $message
        );

        parent::__construct($message, $code);
    }

    public static function becauseOfUnexpectedExceedingSource(
        Range $affectedRangeInSource,
        string $exceedingCharacter
    ): self {
        return new self(
            code: 1691675396,
            message: sprintf(
                'Expected source to end, but found exceeding character "%s".',
                $exceedingCharacter
            ),
            affectedRangeInSource: $affectedRangeInSource
        );
    }
}