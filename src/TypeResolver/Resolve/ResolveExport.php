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

use PackageFactory\ComponentEngine\Parser\Ast\ComponentDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\EnumDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExportNode;
use PackageFactory\ComponentEngine\Parser\Ast\InterfaceDeclarationNode;
use PackageFactory\ComponentEngine\TypeResolver\Scope\Scope;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\ComponentDeclaration;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\EnumDeclaration;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\InterfaceDeclaration;

trait ResolveExport
{
    private readonly Scope $scope;

    abstract private function resolveComponentDeclaration(
        ComponentDeclarationNode $componentDeclarationNode
    ): ComponentDeclaration;

    abstract private function resolveEnumDeclaration(
        EnumDeclarationNode $enumDeclarationNode
    ): EnumDeclaration;

    abstract private function resolveInterfaceDeclaration(
        InterfaceDeclarationNode $interfaceDeclarationNode
    ): InterfaceDeclaration;

    private function resolveExport(ExportNode $exportNode)
    {
        return match ($exportNode->declaration::class) {
            ComponentDeclarationNode::class => $this->resolveComponentDeclaration($exportNode->declaration),
            EnumDeclarationNode::class => $this->resolveEnumDeclaration($exportNode->declaration),
            InterfaceDeclarationNode::class => $this->resolveInterfaceDeclaration($exportNode->declaration)
        };
    }
}
