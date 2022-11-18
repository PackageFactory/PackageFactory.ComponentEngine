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

namespace PackageFactory\ComponentEngine\Target\Php\Transpiler\Module;

use PackageFactory\ComponentEngine\Module\LoaderInterface;
use PackageFactory\ComponentEngine\Parser\Ast\ComponentDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\EnumDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\ModuleNode;
use PackageFactory\ComponentEngine\Parser\Ast\StructDeclarationNode;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\ComponentDeclaration\ComponentDeclarationTranspiler;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\EnumDeclaration\EnumDeclarationTranspiler;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\StructDeclaration\StructDeclarationTranspiler;
use PackageFactory\ComponentEngine\TypeSystem\Scope\ModuleScope\ModuleScope;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;

final class ModuleTranspiler
{
    public function __construct(
        private readonly LoaderInterface $loader,
        private readonly ScopeInterface $globalScope,
        private readonly ModuleStrategyInterface $strategy
    ) {
    }

    public function transpile(ModuleNode $moduleNode): string
    {
        foreach ($moduleNode->exports->items as $exportNode) {
            return match ($exportNode->declaration::class) {
                ComponentDeclarationNode::class => (new ComponentDeclarationTranspiler(
                    scope: new ModuleScope(
                        loader: $this->loader,
                        moduleNode: $moduleNode,
                        parentScope: $this->globalScope
                    ),
                    module: $moduleNode,
                    strategy: $this->strategy->getComponentDeclarationStrategyFor($moduleNode)
                ))->transpile($exportNode->declaration),
                EnumDeclarationNode::class => (new EnumDeclarationTranspiler(
                    strategy: $this->strategy->getEnumDeclarationStrategyFor($moduleNode)
                ))->transpile($exportNode->declaration),
                StructDeclarationNode::class => (new StructDeclarationTranspiler(
                    scope: new ModuleScope(
                        loader: $this->loader,
                        moduleNode: $moduleNode,
                        parentScope: $this->globalScope
                    ),
                    strategy: $this->strategy->getStructDeclarationStrategyFor($moduleNode)
                ))->transpile($exportNode->declaration)
            };
        }

        return '';
    }
}
