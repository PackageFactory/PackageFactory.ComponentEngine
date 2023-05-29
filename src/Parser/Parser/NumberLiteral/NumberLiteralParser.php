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

namespace PackageFactory\ComponentEngine\Parser\Parser\NumberLiteral;

use PackageFactory\ComponentEngine\Definition\NumberFormat;
use PackageFactory\ComponentEngine\Parser\Ast\NumberLiteralNode;
use Parsica\Parsica\Parser;

use function Parsica\Parsica\any;
use function Parsica\Parsica\append;
use function Parsica\Parsica\assemble;
use function Parsica\Parsica\char;
use function Parsica\Parsica\charI;
use function Parsica\Parsica\either;
use function Parsica\Parsica\float;
use function Parsica\Parsica\isCharCode;
use function Parsica\Parsica\isDigit;
use function Parsica\Parsica\isHexDigit;
use function Parsica\Parsica\optional;
use function Parsica\Parsica\stringI;
use function Parsica\Parsica\takeWhile;
use function Parsica\Parsica\takeWhile1;

final class NumberLiteralParser
{
    /** @var Parser<NumberLiteralNode> */
    private static Parser $i;

    /** @return Parser<NumberLiteralNode> */
    public static function get(): Parser
    {
        return self::$i ??= self::initialize();
    }

    /** @return Parser<NumberLiteralNode> */
    private static function initialize(): Parser
    {
        $isOctalDigit = isCharCode(range(0x30, 0x37));
        $isBinaryDigit = isCharCode([0x30, 0x31]);

        $binaryParser = stringI('0b')->append(takeWhile($isBinaryDigit))
            ->map(fn ($value) => new NumberLiteralNode($value, NumberFormat::BINARY));

        $octalParser = stringI('0o')->append(takeWhile($isOctalDigit))
            ->map(fn ($value) => new NumberLiteralNode($value, NumberFormat::OCTAL));

        $hexadecimalParser = stringI('0x')->append(takeWhile(isHexDigit()))
            ->map(fn ($value) => new NumberLiteralNode($value, NumberFormat::HEXADECIMAL));

        $decimalParser = self::float()
            ->map(fn ($value) => new NumberLiteralNode($value, NumberFormat::DECIMAL));

        return any(
            $binaryParser,
            $octalParser,
            $hexadecimalParser,
            $decimalParser,
        );
    }

    /**
     * adjusted from {@see float()}
     */
    private static function float(): Parser
    {
        $digits = takeWhile1(isDigit())->label('at least one 0-9');
        $fraction = char('.')->append($digits);
        $exponent = charI('e')->append($digits);
        return either(
            assemble(
                $digits,
                optional($fraction),
                optional($exponent)
            ),
            append(
                $fraction,
                optional($exponent)
            )
        )->label("float");
    }
}
