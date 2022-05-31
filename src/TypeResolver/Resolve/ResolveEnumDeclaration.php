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

use PackageFactory\ComponentEngine\Parser\Ast\EnumDeclarationNode;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\EnumDeclaration;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\EnumMemberDeclarations;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\EnumMemberDeclaration;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\Identifier;

trait ResolveEnumDeclaration
{
    private function resolveEnumDeclaration(
        EnumDeclarationNode $enumDeclarationNode
    ): EnumDeclaration {
        $memberDeclarations = [];
        foreach ($enumDeclarationNode->memberDeclarations->items as $enumMemberDeclarationNode) {
            $memberDeclarations[] = new EnumMemberDeclaration(
                name: new Identifier($enumMemberDeclarationNode->name)
            );
        }

        return new EnumDeclaration(
            name: new Identifier($enumDeclarationNode->enumName),
            memberDeclarations: new EnumMemberDeclarations(...$memberDeclarations)
        );
    }
}
