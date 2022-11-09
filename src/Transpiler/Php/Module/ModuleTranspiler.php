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

namespace PackageFactory\ComponentEngine\Transpiler\Php\Module;

use PackageFactory\ComponentEngine\Parser\Ast\ComponentDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\EnumDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\ModuleNode;
use PackageFactory\ComponentEngine\Parser\Ast\StructDeclarationNode;
use PackageFactory\ComponentEngine\Transpiler\Php\ComponentDeclaration\ComponentDeclarationTranspiler;
use PackageFactory\ComponentEngine\Transpiler\Php\EnumDeclaration\EnumDeclarationTranspiler;
use PackageFactory\ComponentEngine\Transpiler\Php\StructDeclaration\StructDeclarationTranspiler;

final class ModuleTranspiler
{
    public function transpile(ModuleNode $moduleNode): string
    {
        foreach ($moduleNode->exports->items as $exportNode) {
            return match ($exportNode->declaration::class) {
                ComponentDeclarationNode::class => (new ComponentDeclarationTranspiler())->transpile($exportNode->declaration),
                EnumDeclarationNode::class => (new EnumDeclarationTranspiler())->transpile($exportNode->declaration),
                StructDeclarationNode::class => (new StructDeclarationTranspiler())->transpile($exportNode->declaration)
            };
        }

        return '';
    }
}
