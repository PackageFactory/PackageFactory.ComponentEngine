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

namespace PackageFactory\ComponentEngine\Parser\Parser\Export;

use PackageFactory\ComponentEngine\Parser\Ast\ExportNode;
use PackageFactory\ComponentEngine\Parser\Parser\ComponentDeclaration\ComponentDeclarationParser;
use PackageFactory\ComponentEngine\Parser\Parser\EnumDeclaration\EnumDeclarationParser;
use PackageFactory\ComponentEngine\Parser\Parser\StructDeclaration\StructDeclarationParser;
use PackageFactory\ComponentEngine\Parser\Parser\UtilityParser;
use Parsica\Parsica\Parser;

use function Parsica\Parsica\any;
use function Parsica\Parsica\collect;
use function Parsica\Parsica\skipSpace;

final class ExportParser
{
    /** @return Parser<ExportNode> */
    public static function get(): Parser
    {
        return collect(
            skipSpace(),
            UtilityParser::keyword('export'),
            skipSpace(),
            any(
                ComponentDeclarationParser::get(),
                EnumDeclarationParser::get(),
                StructDeclarationParser::get()
            ),
            skipSpace()
        )->map(fn ($collected) => new ExportNode($collected[3]));
    }
}
