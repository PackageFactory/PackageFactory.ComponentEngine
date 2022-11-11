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

use PackageFactory\ComponentEngine\Module\LoaderInterface;
use PackageFactory\ComponentEngine\Parser\Ast\ComponentDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\ImportNode;
use PackageFactory\ComponentEngine\Parser\Ast\ModuleNode;
use PackageFactory\ComponentEngine\Parser\Ast\StructDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Source\Path;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Tokenizer;
use PackageFactory\ComponentEngine\TypeSystem\Type\ComponentType\ComponentType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StructType\StructType;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

final class ModuleFileLoader implements LoaderInterface
{
    public function resolveTypeOfImport(ImportNode $importNode): TypeInterface
    {
        $pathToImportFrom = $importNode->source->path->resolveRelationTo(
            Path::fromString($importNode->path)
        );
        $source = Source::fromFile($pathToImportFrom->value);
        $tokenizer = Tokenizer::fromSource($source);
        $module = ModuleNode::fromTokens($tokenizer->getIterator());
        $export = $module->exports->get($importNode->name->value);

        if ($export === null) {
            throw new \Exception(
                '@TODO: Module "' . $importNode->path . '" has no exported member "' . $importNode->name->value . '".'
            );
        }

        return match ($export->declaration::class) {
            ComponentDeclarationNode::class => ComponentType::fromComponentDeclarationNode($export->declaration),
            StructDeclarationNode::class => StructType::fromStructDeclarationNode($export->declaration),
            default => throw new \Exception('@TODO: Get type of ' . $export->declaration::class)
        };
    }
}
