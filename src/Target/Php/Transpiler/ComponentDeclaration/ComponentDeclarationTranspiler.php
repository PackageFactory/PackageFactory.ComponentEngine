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

namespace PackageFactory\ComponentEngine\Target\Php\Transpiler\ComponentDeclaration;

use PackageFactory\ComponentEngine\Language\AST\Node\ComponentDeclaration\ComponentDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Module\ModuleNode;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\Expression\ExpressionTranspiler;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\TypeReference\TypeReferenceTranspiler;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\Expression\ExpressionTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Scope\ComponentScope\ComponentScope;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;

final class ComponentDeclarationTranspiler
{
    public function __construct(
        private readonly ScopeInterface $scope,
        private readonly ModuleNode $module,
        private readonly ComponentDeclarationStrategyInterface $strategy
    ) {
    }

    public function transpile(ComponentDeclarationNode $componentDeclarationNode): string
    {
        $className = $this->strategy->getClassNameFor($componentDeclarationNode);
        $baseClassName = $this->strategy->getBaseClassNameFor($componentDeclarationNode);

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

        foreach ($this->module->imports->items as $importNode) {
            // @TODO: Generate Namespaces + Name via TypeReferenceStrategyInterface Dynamically
            foreach ($importNode->names->items as $name) {
                $lines[] = 'use Vendor\\Project\\Component\\' . $name->value->value . ';';
            }
        }

        $lines[] = '';
        $lines[] = $baseClassName
            ? 'final class ' . $className->getShortClassName() . ' extends ' . $baseClassName->getShortClassName()
            : 'final class ' . $className->getShortClassName();
        $lines[] = '{';

        if (!$componentDeclarationNode->props->isEmpty()) {
            $lines[] = '    public function __construct(';
            $lines[] = $this->writeConstructorPropertyDeclarations($componentDeclarationNode);
            $lines[] = '    ) {';
            $lines[] = '    }';
            $lines[] = '';
        }

        $lines[] = '    public function render(): string';
        $lines[] = '    {';
        $lines[] = $this->writeReturnExpression($componentDeclarationNode);
        $lines[] = '    }';
        $lines[] = '}';
        $lines[] = '';

        return join("\n", $lines);
    }

    public function writeConstructorPropertyDeclarations(ComponentDeclarationNode $componentDeclarationNode): string
    {
        $typeReferenceTranspiler = new TypeReferenceTranspiler(
            scope: $this->scope,
            strategy: $this->strategy->getTypeReferenceStrategyFor($componentDeclarationNode)
        );
        $propertyDeclarations = $componentDeclarationNode->props;
        $lines = [];

        foreach ($propertyDeclarations->items as $propertyDeclaration) {
            $lines[] = '        public readonly ' . $typeReferenceTranspiler->transpile($propertyDeclaration->type) . ' $' . $propertyDeclaration->name->value->value . ',';
        }

        if ($length = count($lines)) {
            $lines[$length - 1] = substr($lines[$length - 1], 0, -1);
        }

        return join("\n", $lines);
    }

    public function writeReturnExpression(ComponentDeclarationNode $componentDeclarationNode): string
    {
        $componentScope = new ComponentScope(
            componentDeclarationNode: $componentDeclarationNode,
            parentScope: $this->scope
        );
        $expressionTypeResolver = new ExpressionTypeResolver(
            scope: $componentScope
        );
        $expressionTranspiler = new ExpressionTranspiler(
            scope: $componentScope,
            shouldAddQuotesIfNecessary: true
        );

        $returnExpression = $componentDeclarationNode->return;
        $returnTypeIsString = StringType::get()->is(
            $expressionTypeResolver->resolveTypeOf($returnExpression)
        );
        $transpiledReturnExpression = $expressionTranspiler->transpile($returnExpression);

        if (!$returnTypeIsString) {
            $transpiledReturnExpression = '(string) ' . $transpiledReturnExpression;
        }

        return '        return ' . $transpiledReturnExpression . ';';
    }
}
