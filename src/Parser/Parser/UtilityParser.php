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

use PackageFactory\ComponentEngine\Parser\Source\Path;
use Parsica\Parsica\Internal\Fail;
use Parsica\Parsica\Internal\Succeed;
use Parsica\Parsica\Parser;
use Parsica\Parsica\ParseResult;
use Parsica\Parsica\Stream;

use function Parsica\Parsica\alphaChar;
use function Parsica\Parsica\alphaNumChar;
use function Parsica\Parsica\anySingle;
use function Parsica\Parsica\append;
use function Parsica\Parsica\char;
use function Parsica\Parsica\either;
use function Parsica\Parsica\isSpace;
use function Parsica\Parsica\oneOfS;
use function Parsica\Parsica\string;
use function Parsica\Parsica\takeWhile;
use function Parsica\Parsica\takeWhile1;
use function Parsica\Parsica\zeroOrMore;

final class UtilityParser
{
    public static function identifier(): Parser
    {
        return alphaChar()->append(zeroOrMore(alphaNumChar()));
    }

    public static function keyword(string $keyword): Parser
    {
        return string($keyword)->notFollowedBy(alphaNumChar());
    }

    public static function skipSpaceAndComments(): Parser
    {
        return zeroOrMore(
            either(
                takeWhile1(isSpace()),
                char('#')->then(takeWhile(fn ($char) => $char !== "\n"))
            )
        )->voidLeft(null);
    }

    /**
     * @return Parser<string>
     */
    public static function quotedStringContents(): Parser
    {
        // labels, and escaping handling
        return oneOfS('"\'')->bind(
            fn (string $startingQuoteChar) => append(
                $simpleCase = takeWhile(
                    fn (string $char): bool => $char !== $startingQuoteChar && $char !== '\\'
                ),
                zeroOrMore(
                    append(
                        char('\\')->followedBy(anySingle()),
                        $simpleCase,
                    )
                )
            )->thenIgnore(char($startingQuoteChar))
        );
    }

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

    /**
     * Map a function over the parser (which in turn maps it over the result).
     *
     * @template T1
     * @template T2
     * @psalm-param Parser<T1> $parser
     * @psalm-param callable(T1, Path) : T2 $transformWithPath
     * @psalm-return Parser<T2>
     */
    public static function mapWithPath(Parser $parser, callable $transformWithPath): Parser
    {
        return Parser::make($parser->getLabel(), function (Stream $stream) use($parser, $transformWithPath) {
            $fileName = $stream->position()->filename();
            $path = match ($fileName) {
                '<input>' => Path::createMemory(),
                default => Path::fromString($fileName)
            };
            return $parser->run($stream)->map(fn ($output) => $transformWithPath($output, $path));
        });
    }
}
