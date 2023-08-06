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

namespace PackageFactory\ComponentEngine\Target\Php\Transpiler\ValueReference;

use PackageFactory\ComponentEngine\Language\AST\Node\ValueReference\ValueReferenceNode;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumStaticType;

final class ValueReferenceTranspiler
{
    public function __construct(private readonly ScopeInterface $scope)
    {
    }

    public function transpile(ValueReferenceNode $identifierNode): string
    {
        $typeOfIdentifiedValue = $this->scope->getTypeOf($identifierNode->name);

        return match (true) {
            // @TODO: Generate Name via TypeReferenceStrategyInterface Dynamically
            $typeOfIdentifiedValue instanceof EnumStaticType => $identifierNode->name->value,
            default => '$this->' . $identifierNode->name->value
        };
    }
}
