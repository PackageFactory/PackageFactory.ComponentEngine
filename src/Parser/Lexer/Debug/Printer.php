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

namespace PackageFactory\ComponentEngine\Parser\Lexer\Debug;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;

final class Printer
{
    /**
     * @param \Iterator<Token> $tokenStream
     * @return void
     */
    public static function print(\Iterator $tokenStream): void
    {
        $lines = [''];
        echo sprintf(
            "%-40s %-5s %-5s %-5s %-5s %-5s %-5s %s",
            'TYPE',
            'S_IDX',
            'S_ROW',
            'S_COL',
            'E_IDX',
            'E_ROW',
            'E_COL',
            'VALUE'
        ) . PHP_EOL;
        foreach ($tokenStream as $token) {
            echo sprintf(
                "%-40s %-5s %-5s %-5s %-5s %-5s %-5s %s",
                $token->type->name,
                $token->start->index,
                $token->start->rowIndex,
                $token->start->columnIndex,
                $token->end->index,
                $token->end->rowIndex,
                $token->end->columnIndex,
                str_replace("\n", " ", $token->value)
            ) . PHP_EOL;
        }
    }
}
