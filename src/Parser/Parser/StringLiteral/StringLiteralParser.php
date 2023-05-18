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

namespace PackageFactory\ComponentEngine\Parser\Parser\StringLiteral;

use PackageFactory\ComponentEngine\Parser\Ast\StringLiteralNode;
use Parsica\Parsica\Parser;

use function Parsica\Parsica\{anySingle, append, between, char, assemble, either, takeWhile, zeroOrMore};

final class StringLiteralParser
{
    private static ?Parser $instance = null;

    public static function get(): Parser
    {
        return self::$instance ??= self::build();
    }

    private static function build(): Parser
    {
        return either(
            self::forQuotedStringType('\''),
            self::forQuotedStringType('"')
        )->map(fn (string $contents) => new StringLiteralNode($contents));
    }

    private static function forQuotedStringType(string $qouteType): Parser
    {
        assert($qouteType === '"' || $qouteType === '\'');
        $takeAllNonBackslashesAndQuoteChars = takeWhile(
            fn (string $char): bool => $char !== $qouteType && $char !== '\\'
        );
        return between(
            char($qouteType),
            char($qouteType),
            append(
                $takeAllNonBackslashesAndQuoteChars,
                zeroOrMore(
                    append(
                        char("\\")->followedBy(anySingle()),
                        $takeAllNonBackslashesAndQuoteChars,
                    )
                )
            )
        );
    }
}
