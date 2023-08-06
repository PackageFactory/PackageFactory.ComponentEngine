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
use PackageFactory\ComponentEngine\Module\ModuleInterface;
use PackageFactory\ComponentEngine\Test\Unit\Module\Fixtures\DummyModule;
use PackageFactory\ComponentEngine\TypeSystem\AtomicTypeInterface;

final class DummyLoader implements LoaderInterface
{
    /**
     * @var array<string,DummyModule>
     */
    private readonly array $data;

    /**
     * @param array<string,array<string,AtomicTypeInterface>> $data
     */
    public function __construct(array $data = [])
    {
        $thisData = [];
        foreach ($data as $key => $moduleData) {
            $thisData[$key] = new DummyModule($moduleData);
        }

        $this->data = $thisData;
    }

    public function loadModule(string $pathToModule): ModuleInterface
    {
        if ($module = $this->data[$pathToModule] ?? null) {
            return $module;
        }

        throw new \Exception(
            '[DummyLoader] Module at path "' . $pathToModule . '" does not exist'
        );
    }
}
