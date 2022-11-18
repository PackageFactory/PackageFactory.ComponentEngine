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

namespace PackageFactory\ComponentEngine\Target\Php\Transpiler\StructDeclaration;

use PackageFactory\ComponentEngine\Parser\Ast\StructDeclarationNode;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\TypeReference\TypeReferenceTranspiler;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;

final class StructDeclarationTranspiler
{
    public function __construct(
        private readonly ScopeInterface $scope,
        private readonly StructDeclarationStrategyInterface $strategy
    ) {
    }

    public function transpile(StructDeclarationNode $structDeclarationNode): string
    {
        $className = $this->strategy->getClassNameFor($structDeclarationNode);
        $baseClassName = $this->strategy->getBaseClassNameFor($structDeclarationNode);

        $lines = [];

        $lines[] = '<?php';
        $lines[] = '';
        $lines[] = 'declare(strict_types=1);';
        $lines[] = '';
        $lines[] = 'namespace ' . $className->getNamespace() . ';';
        $lines[] = '';

        if ($baseClassName) {
            $lines[] = 'use ' . $baseClassName->getFullyQualifiedClassName() . ';';
        }

        $lines[] = '';
        $lines[] = $baseClassName
            ? 'final class ' . $className->getShortClassName() . ' extends ' . $baseClassName->getShortClassName()
            : 'final class ' . $className->getShortClassName();
        $lines[] = '{';

        if (!$structDeclarationNode->propertyDeclarations->isEmpty()) {
            $lines[] = '    public function __construct(';
            $lines[] = $this->writeConstructorPropertyDeclarations($structDeclarationNode);
            $lines[] = '    ) {';
            $lines[] = '    }';
        }

        $lines[] = '}';
        $lines[] = '';

        return join("\n", $lines);
    }

    public function writeConstructorPropertyDeclarations(StructDeclarationNode $structDeclarationNode): string
    {
        $typeReferenceTranspiler = new TypeReferenceTranspiler(
            scope: $this->scope,
            strategy: $this->strategy->getTypeReferenceStrategyFor($structDeclarationNode)
        );
        $propertyDeclarations = $structDeclarationNode->propertyDeclarations;
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
