<?php

/**
 * PackageFactory.ComponentEngine - Universal View Components for PHP
 *   Copyright (C) 2023 Contributors of PackageFactory.ComponentEngine
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

namespace PackageFactory\ComponentEngine\Test\Unit\Module\Fixtures;

use PackageFactory\ComponentEngine\Domain\VariableName\VariableName;
use PackageFactory\ComponentEngine\Module\ModuleInterface;
use PackageFactory\ComponentEngine\TypeSystem\AtomicTypeInterface;

final class DummyModule implements ModuleInterface
{
    /**
     * @param array<string,AtomicTypeInterface> $data
     */
    public function __construct(private readonly array $data = [])
    {
    }

    public function getTypeOf(VariableName $exportName): AtomicTypeInterface
    {
        if ($type = $this->data[$exportName->value] ?? null) {
            return $type;
        }

        throw new \Exception(
            '[DummyModule] Module does not export "' . $exportName->value . '".'
        );
    }
}
