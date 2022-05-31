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

namespace PackageFactory\ComponentEngine\TypeResolver\Resolve;

use PackageFactory\ComponentEngine\Parser\Ast\PropertyDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\TypeReferenceNode;
use PackageFactory\ComponentEngine\Type\Type;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\Identifier;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\PropertyDeclaration;

trait ResolvePropertyDeclaration
{
    abstract private function resolveTypeReference(
        TypeReferenceNode $typeReferenceNode
    ): Type;

    private function resolvePropertyDeclaration(
        PropertyDeclarationNode $propertyDeclarationNode
    ): PropertyDeclaration {
        return new PropertyDeclaration(
            name: new Identifier($propertyDeclarationNode->name),
            type: $this->resolveTypeReference($propertyDeclarationNode->type)
        );
    }
}
