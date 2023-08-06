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

namespace PackageFactory\ComponentEngine\Module\Loader\ModuleFile;

use PackageFactory\ComponentEngine\Language\Parser\Module\ModuleParser;
use PackageFactory\ComponentEngine\Module\LoaderInterface;
use PackageFactory\ComponentEngine\Module\ModuleId;
use PackageFactory\ComponentEngine\Module\ModuleInterface;
use PackageFactory\ComponentEngine\Parser\Source\Path;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Tokenizer;

final class ModuleFileLoader implements LoaderInterface
{
    public function __construct(
        private readonly Path $sourcePath
    ) {
    }

    public function loadModule(string $pathToModule): ModuleInterface
    {
        $pathToImportFrom = $this->sourcePath->resolveRelationTo(
            Path::fromString($pathToModule)
        );
        $source = Source::fromFile($pathToImportFrom->value);
        $tokenizer = Tokenizer::fromSource($source);
        $tokens = $tokenizer->getIterator();

        $moduleParser = ModuleParser::singleton();

        $moduleId = ModuleId::fromSource($source);
        $moduleNode = $moduleParser->parse($tokens);


        return new Module(
            moduleId: $moduleId,
            moduleNode: $moduleNode
        );
    }
}
