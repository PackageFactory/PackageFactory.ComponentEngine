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

namespace PackageFactory\ComponentEngine\Parser\Parser\EnumMemberDeclaration;

use PackageFactory\ComponentEngine\Parser\Ast\EnumMemberDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\EnumMemberDeclarationNodes;
use PackageFactory\ComponentEngine\Parser\Parser\NumberLiteral\NumberLiteralParser;
use PackageFactory\ComponentEngine\Parser\Parser\StringLiteral\StringLiteralParser;
use PackageFactory\ComponentEngine\Parser\Parser\UtilityParser;
use Parsica\Parsica\Parser;

use function Parsica\Parsica\between;
use function Parsica\Parsica\char;
use function Parsica\Parsica\collect;
use function Parsica\Parsica\either;
use function Parsica\Parsica\many;
use function Parsica\Parsica\optional;
use function Parsica\Parsica\skipSpace;

final class EnumMemberDeclarationParser
{
    /** @return Parser<EnumMemberDeclarationNodes> */
    public static function get(): Parser
    {
        return many(
            collect(
                UtilityParser::identifier(),
                skipSpace(),
                optional(
                  between(
                      char('('),
                      char(')'),
                      either(
                          StringLiteralParser::get(),
                          NumberLiteralParser::get()
                      )
                  )
                ),
                skipSpace()
            )->map(fn ($collected) => new EnumMemberDeclarationNode($collected[0], $collected[2]))
        )->map(fn ($collected) => new EnumMemberDeclarationNodes(...$collected ?? []));
    }
}
