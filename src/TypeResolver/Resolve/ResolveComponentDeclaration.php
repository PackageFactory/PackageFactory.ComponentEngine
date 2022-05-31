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
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\TypeResolver\Scope\BlockScope;
use PackageFactory\ComponentEngine\TypeResolver\Scope\Scope;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\ComponentDeclaration;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\Expression;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\InterfaceDeclaration;

trait ResolveComponentDeclaration
{
    private readonly Scope $scope;

    abstract private function withScope(Scope $scope): self;

    abstract private function resolveComponentInterface(
        ComponentDeclarationNode $componentDeclarationNode
    ): InterfaceDeclaration;

    abstract private function resolveExpression(
        ExpressionNode $expressionNode
    ): Expression;

    private function resolveComponentDeclaration(
        ComponentDeclarationNode $componentDeclarationNode
    ): ComponentDeclaration {
        $interface = $this->resolveComponentInterface($componentDeclarationNode);
        $return = $this
            ->withScope(
                $this->scope->push(
                    BlockScope::fromRecordType($interface->toRecordType())
                )
            )
            ->resolveExpression($componentDeclarationNode->returnExpression);

        return new ComponentDeclaration(
            interface: $interface,
            return: $return
        );
    }
}
