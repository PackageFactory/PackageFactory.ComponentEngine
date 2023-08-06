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

namespace PackageFactory\ComponentEngine\Test\Unit\Target\Php\Transpiler\StructDeclaration;

use PackageFactory\ComponentEngine\Language\AST\Node\StructDeclaration\StructDeclarationNode;
use PackageFactory\ComponentEngine\Target\Php\TargetSpecific\ClassName;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\StructDeclaration\StructDeclarationStrategyInterface;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\TypeReference\TypeReferenceStrategyInterface;
use PackageFactory\ComponentEngine\Test\Unit\Target\Php\Transpiler\TypeReference\TypeReferenceTestStrategy;

final class StructDeclarationTestStrategy implements StructDeclarationStrategyInterface
{
    public function getClassNameFor(StructDeclarationNode $structDeclarationNode): ClassName
    {
        return ClassName::fromString('Vendor\\Project\\Component\\' . $structDeclarationNode->name->value->value);
    }

    public function getBaseClassNameFor(StructDeclarationNode $structDeclarationNode): ?ClassName
    {
        return ClassName::fromString('Vendor\\Project\\BaseClass');
    }

    public function getTypeReferenceStrategyFor(StructDeclarationNode $structDeclarationNode): TypeReferenceStrategyInterface
    {
        return new TypeReferenceTestStrategy();
    }
}
