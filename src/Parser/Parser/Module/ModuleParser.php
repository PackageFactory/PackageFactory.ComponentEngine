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

namespace PackageFactory\ComponentEngine\Parser\Parser\Module;

use PackageFactory\ComponentEngine\Parser\Ast\ExportNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExportNodes;
use PackageFactory\ComponentEngine\Parser\Ast\ImportNode;
use PackageFactory\ComponentEngine\Parser\Ast\ImportNodes;
use PackageFactory\ComponentEngine\Parser\Ast\ModuleNode;
use PackageFactory\ComponentEngine\Parser\Parser\Export\ExportParser;
use PackageFactory\ComponentEngine\Parser\Parser\Import\ImportParser;
use PackageFactory\ComponentEngine\Parser\Source\Path;
use Parsica\Parsica\Parser;

use function Parsica\Parsica\either;
use function Parsica\Parsica\many;
use function Parsica\Parsica\skipSpace;

final class ModuleParser
{
    private static ?Parser $instance = null;

    public static function fromPath(Path $path): ModuleNode
    {

    }

    public static function parseFromString(string $string): ModuleNode
    {
        return self::get(Path::createMemory())->thenEof()->tryString($string)->output();
    }

    public static function get(Path $path): Parser
    {
        return many(
            either(
                ImportParser::get($path),
                ExportParser::get()
            )
        )->map(function ($collected) {
            $importNodes = ImportNodes::empty();
            $exportNodes = [];
            foreach ($collected as $item) {
                match ($item::class) {
                    ImportNodes::class => $importNodes = $importNodes->merge($item),
                    ExportNode::class => $exportNodes[] = $item
                };
            }
            return new ModuleNode(
                $importNodes,
                new ExportNodes(...$exportNodes)
            );
        });
    }
}
