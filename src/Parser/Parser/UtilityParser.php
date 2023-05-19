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

use Parsica\Parsica\Internal\Fail;
use Parsica\Parsica\Internal\Succeed;
use Parsica\Parsica\Parser;
use Parsica\Parsica\ParseResult;
use Parsica\Parsica\Stream;

final class UtilityParser
{
    /**
     * A multi character compatible version (eg. for strings) of {@see oneOf()}
     *
     * While one could leverage multiple string parsers, it's not really performance efficient:
     *
     *  any(string('f'), string('bar'))
     *
     * the above can be rewritten like:
     *
     *  strings(['f', 'bar'])
     *
     * @param array<int, string> $strings
     * @return Parser<string>
     */
    public static function strings(array $strings): Parser
    {
        $longestString = 0;
        foreach ($strings as $string) {
            $len = mb_strlen($string);
            if ($longestString < $len) {
                $longestString = $len;
            }
        }
        return Parser::make('strings', function (Stream $stream) use($strings, $longestString): ParseResult {
            if ($stream->isEOF()) {
                return new Fail('strings', $stream);
            }
            $result = $stream->takeN($longestString);
            foreach ($strings as $string) {
                if (str_starts_with($result->chunk(), $string)) {
                    return new Succeed($string, $stream->takeN(mb_strlen($string))->stream());
                }
            }
            return new Fail('strings', $stream);
        });
    }
}
