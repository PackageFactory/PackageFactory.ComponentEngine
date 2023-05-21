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

namespace PackageFactory\ComponentEngine\Parser\Parser\PropertyDeclaration;

use PackageFactory\ComponentEngine\Parser\Ast\PropertyDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\PropertyDeclarationNodes;
use PackageFactory\ComponentEngine\Parser\Parser\TypeReference\TypeReferenceParser;
use PackageFactory\ComponentEngine\Parser\Parser\UtilityParser;
use Parsica\Parsica\Parser;

use function Parsica\Parsica\char;
use function Parsica\Parsica\collect;
use function Parsica\Parsica\many;
use function Parsica\Parsica\skipSpace;

final class PropertyDeclarationParser
{
    /** @return Parser<PropertyDeclarationNodes> */
    public static function get(): Parser
    {
        return many(
            collect(
                UtilityParser::identifier(),
                skipSpace(),
                char(':'),
                skipSpace(),
                TypeReferenceParser::get(),
                skipSpace()
            )->map(fn ($collected) => new PropertyDeclarationNode($collected[0], $collected[4]))
        )->map(fn ($collected) => new PropertyDeclarationNodes(...$collected ?? []));
    }
}
