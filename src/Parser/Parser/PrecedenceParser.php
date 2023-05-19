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

namespace PackageFactory\ComponentEngine\Parser\Parser;

use PackageFactory\ComponentEngine\Definition\Precedence;
use Parsica\Parsica\Parser;

use function Parsica\Parsica\either;
use function Parsica\Parsica\fail;
use function Parsica\Parsica\lookAhead;
use function Parsica\Parsica\pure;
use function Parsica\Parsica\succeed;

final class PrecedenceParser
{
    /**
     * @var list<array{0: list<string>, 1: Precedence}>
     */
    private const STRINGS_TO_PRECEDENCE = [
        [['(', ')', '[', ']', '?.', '.'], Precedence::ACCESS],
        [['!'], Precedence::UNARY],
        [['*', '/', '%'], Precedence::POINT],
        [['+', '-'], Precedence::DASH],
        [['+', '-'], Precedence::DASH],
        [['>', '>=', '<', '<='], Precedence::COMPARISON],
        [['===', '!=='], Precedence::EQUALITY],
        [['&&'], Precedence::LOGICAL_AND],
        [['||'], Precedence::LOGICAL_OR],
        [['?', ':'], Precedence::TERNARY]
    ];

    private static ?Parser $precedenceLookahead = null;

    /**
     * Look ahead to see if the precedence from the next characters is less than the given
     *
     * @return Parser<null>
     */
    public static function hasPrecedence(Precedence $precedence): Parser
    {
        return self::precedenceLookahead()->bind(function (Precedence $precedenceByNextCharacters) use ($precedence) {
            if ($precedence->mustStopAt($precedenceByNextCharacters)) {
                return fail($precedence->name . ' must stop at ' . $precedenceByNextCharacters->name);
            }
            return succeed();
        })->label('precedence(' . $precedence->name . ')');
    }

    /**
     * @return Parser<Precedence>
     */
    private static function precedenceLookahead(): Parser
    {
        if (self::$precedenceLookahead) {
            return self::$precedenceLookahead;
        }
        $allStrings = [];
        $stringToPrecedence = [];
        foreach (self::STRINGS_TO_PRECEDENCE as [$strings, $precedence]) {
            foreach ($strings as $string) {
                $allStrings[] = $string;
                $stringToPrecedence[$string] = $precedence;
            }
        }
        return self::$precedenceLookahead = either(
            lookAhead(
                UtilityParser::strings($allStrings)->map(function ($match) use($stringToPrecedence) {
                    return $stringToPrecedence[$match];
                })
            ),
            pure(Precedence::SEQUENCE)
        );
    }
}
