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

namespace PackageFactory\ComponentEngine\Test\Unit\Module\Loader\Fixtures;

use PackageFactory\ComponentEngine\Module\LoaderInterface;
use PackageFactory\ComponentEngine\Parser\Ast\ImportNode;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

final class DummyLoader implements LoaderInterface
{
    /**
     * @param array<string,array<string,TypeInterface>> $data
     */
    public function __construct(private readonly array $data = [])
    {
    }

    public function resolveTypeOfImport(ImportNode $importNode): TypeInterface
    {
        if ($moduleData = $this->data[$importNode->path] ?? null) {
            if ($type = $moduleData[$importNode->name->value] ?? null) {
                return $type;
            }

            throw new \Exception(
                '[DummyLoader] Cannot import "' . $importNode->name->value . '" from "' . $importNode->source->path->value . '"'
            );
        }

        throw new \Exception(
            '[DummyLoader] Unknown Import: ' . json_encode($importNode, JSON_PRETTY_PRINT)
        );
    }
}