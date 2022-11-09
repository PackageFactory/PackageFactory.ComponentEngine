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

namespace PackageFactory\ComponentEngine\Transpiler\Php\StructDeclaration;

use PackageFactory\ComponentEngine\Parser\Ast\StructDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\PropertyDeclarationNodes;
use PackageFactory\ComponentEngine\Transpiler\Php\TypeReference\TypeReferenceTranspiler;

final class StructDeclarationTranspiler
{
    public function transpile(StructDeclarationNode $structDeclarationNode): string
    {
        $lines = [];

        $lines[] = '<?php';
        $lines[] = '';
        $lines[] = 'declare(strict_types=1);';
        $lines[] = '';
        $lines[] = 'namespace Vendor\\Project\\Component;';
        $lines[] = '';
        $lines[] = 'use Vendor\\Project\\BaseClass;';
        $lines[] = '';
        $lines[] = 'final class ' . $structDeclarationNode->structName . ' extends BaseClass';
        $lines[] = '{';

        if (!$structDeclarationNode->propertyDeclarations->isEmpty()) {
            $lines[] = '    public function __construct(';
            $lines[] = $this->writeConstructorPropertyDeclarations($structDeclarationNode->propertyDeclarations);
            $lines[] = '    ) {';
            $lines[] = '    }';
        }

        $lines[] = '}';
        $lines[] = '';

        return join("\n", $lines);
    }

    public function writeConstructorPropertyDeclarations(PropertyDeclarationNodes $propertyDeclarations): string
    {
        $typeReferenceTranspiler = new TypeReferenceTranspiler();
        $lines = [];

        foreach ($propertyDeclarations->items as $propertyDeclaration) {
            $lines[] = '        public readonly ' . $typeReferenceTranspiler->transpile($propertyDeclaration->type) . ' $' . $propertyDeclaration->name . ',';
        }

        if ($length = count($lines)) {
            $lines[$length - 1] = substr($lines[$length - 1], 0, -1);
        }

        return join("\n", $lines);
    }
}
