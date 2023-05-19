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

namespace PackageFactory\ComponentEngine\Parser\Parser\Import;

use PackageFactory\ComponentEngine\Parser\Ast\IdentifierNode;
use PackageFactory\ComponentEngine\Parser\Ast\ImportNode;
use PackageFactory\ComponentEngine\Parser\Ast\ImportNodes;
use PackageFactory\ComponentEngine\Parser\Parser\Identifier\IdentifierParser;
use PackageFactory\ComponentEngine\Parser\Parser\UtilityParser;
use PackageFactory\ComponentEngine\Parser\Source\Path;
use Parsica\Parsica\Parser;

use function Parsica\Parsica\between;
use function Parsica\Parsica\char;
use function Parsica\Parsica\collect;
use function Parsica\Parsica\sepBy1;
use function Parsica\Parsica\skipSpace;

final class ImportParser
{
    /** @return Parser<ImportNodes> */
    public static function get(): Parser
    {
        return UtilityParser::mapWithPath(
            collect(
                skipSpace(),
                UtilityParser::keyword('from'),
                skipSpace(),
                UtilityParser::quotedStringContents(),
                skipSpace(),
                UtilityParser::keyword('import'),
                skipSpace(),
                char('{'),
                skipSpace(),
                sepBy1(
                    between(skipSpace(), skipSpace(), char(',')),
                    IdentifierParser::get(),
                ),
                skipSpace(),
                char('}'),
                skipSpace(),
            ),
            fn (array $collected, Path $sourcePath) => new ImportNodes(
                ...array_map(
                    fn (IdentifierNode $name) => new ImportNode(
                        sourcePath: $sourcePath,
                        path: $collected[3],
                        name: $name
                    ),
                    $collected[9],
                )
            )
        );
    }
}
