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

namespace PackageFactory\ComponentEngine\Parser\Parser\Attribute;

use PackageFactory\ComponentEngine\Parser\Ast\AttributeNode;
use PackageFactory\ComponentEngine\Parser\Ast\AttributeNodes;
use PackageFactory\ComponentEngine\Parser\Parser\Expression\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Parser\StringLiteral\StringLiteralParser;
use PackageFactory\ComponentEngine\Parser\Parser\UtilityParser;
use Parsica\Parsica\Parser;

use function Parsica\Parsica\between;
use function Parsica\Parsica\char;
use function Parsica\Parsica\collect;
use function Parsica\Parsica\either;
use function Parsica\Parsica\many;
use function Parsica\Parsica\skipSpace;

final class AttributeParser
{
    /** @return Parser<AttributeNodes> */
    public static function get(): Parser
    {
        return many(
            collect(
                skipSpace(),
                self::attributeIdentifier(),
                char('='),
                either(
                    StringLiteralParser::get(),
                    between(
                        char('{'),
                        char('}'),
                        ExpressionParser::get()
                    )
                )
            )->map(fn ($collected) => new AttributeNode($collected[1], $collected[3]))
        )->map(fn ($collected) => new AttributeNodes(...$collected ?? []));
    }

    private static function attributeIdentifier(): Parser
    {
        return UtilityParser::identifier();
    }
}
