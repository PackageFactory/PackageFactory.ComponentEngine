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

namespace PackageFactory\ComponentEngine\Parser\Ast\Module\Import;

final class Imports implements \JsonSerializable
{
    /**
     * @var array<string,Import>
     */
    private readonly array $imports;

    private function __construct(
        Import ...$imports
    ) {
        $this->imports = [];
        foreach ($imports as $import) {
            $this->imports[$import->name] = $import;
        }
    }

    public static function from(Import ...$imports): self
    {
        return new self(...$imports);
    }

    public function jsonSerialize(): mixed
    {
        return $this->imports;
    }
}
