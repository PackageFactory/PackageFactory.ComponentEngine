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

namespace PackageFactory\ComponentEngine\Transpiler\Php;

use PackageFactory\ComponentEngine\Parser\Ast\ModuleNode;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\ComponentDeclaration;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\InterfaceDeclaration;
use PackageFactory\ComponentEngine\TypeResolver\TypeResolver;

final class Transpiler
{
    public function __construct(
        private readonly TypeResolver $typeResolver
    ) {
    }

    public function transpile(ModuleNode $moduleNode): string
    {
        foreach ($moduleNode->exports->items as $exportNode) {
            $typedAst = $this->typeResolver->getTypedAstForExport($exportNode);
            return match ($typedAst::class) {
                ComponentDeclaration::class => $this->transpileComponentDeclaration($moduleNode, $typedAst),
                default => throw new \Exception('@TODO: Transpile ' . $typedAst::class)
            };
        }

        return '';
    }

    public function transpileComponentDeclaration(ModuleNode $moduleNode, ComponentDeclaration $componentDeclaration): string
    {
        $lines = [];

        $lines[] = '<?php';
        $lines[] = '';
        $lines[] = 'declare(strict_types=1);';
        $lines[] = '';
        $lines[] = 'namespace Vendor\\Project\\Component;';
        $lines[] = '';
        $lines[] = 'use Vendor\\Project\\BaseClass;';
        $lines[] = 'use Vendor\\Project\\Hyperscript;';
        $lines[] = '';
        $lines[] = 'final class ' . $componentDeclaration->interface->name->value . ' extends BaseClass';
        $lines[] = '{';
        $lines[] = '    public function __construct(';
        $lines[] = $this->constructorPropertyDeclarations($componentDeclaration->interface);
        $lines[] = '    ) {';
        $lines[] = '    }';
        $lines[] = '';
        $lines[] = '    public function render(): string';
        $lines[] = '    {';
        $lines[] = '    }';
        $lines[] = '}';
        $lines[] = '';

        return join("\n", $lines);
    }

    public function constructorPropertyDeclarations(InterfaceDeclaration $interfaceDeclaration): string
    {
        $lines = [];

        foreach ($interfaceDeclaration->properties->items as $propertyDeclaration) {
            $lines[] = '        public readonly ' . $propertyDeclaration->type . ' $' . $propertyDeclaration->name->value . ',';
        }

        if ($length = count($lines)) {
            $lines[$length - 1] = substr($lines[$length - 1], 0, -1);
        }

        return join("\n", $lines);
    }
}
