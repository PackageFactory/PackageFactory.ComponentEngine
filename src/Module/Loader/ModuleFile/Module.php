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

namespace PackageFactory\ComponentEngine\Module\Loader\ModuleFile;

use PackageFactory\ComponentEngine\Domain\VariableName\VariableName;
use PackageFactory\ComponentEngine\Language\AST\Node\ComponentDeclaration\ComponentDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Module\ModuleNode;
use PackageFactory\ComponentEngine\Language\AST\Node\PropertyDeclaration\PropertyDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StructDeclaration\StructDeclarationNode;
use PackageFactory\ComponentEngine\Module\ModuleId;
use PackageFactory\ComponentEngine\Module\ModuleInterface;
use PackageFactory\ComponentEngine\TypeSystem\AtomicTypeInterface;
use PackageFactory\ComponentEngine\TypeSystem\Type\ComponentType\ComponentType;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumStaticType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StructType\Properties;
use PackageFactory\ComponentEngine\TypeSystem\Type\StructType\Property;
use PackageFactory\ComponentEngine\TypeSystem\Type\StructType\StructType;
use PackageFactory\ComponentEngine\TypeSystem\TypeReference;

final class Module implements ModuleInterface
{
    public function __construct(
        private readonly ModuleId $moduleId,
        private readonly ModuleNode $moduleNode
    ) {
    }

    public function getTypeOf(VariableName $exportName): AtomicTypeInterface
    {
        $exportNode = $this->moduleNode->export;

        return match ($exportNode->declaration::class) {
            ComponentDeclarationNode::class => ComponentType::fromComponentDeclarationNode($exportNode->declaration),
            EnumDeclarationNode::class => EnumStaticType::fromModuleIdAndDeclaration(
                $this->moduleId,
                $exportNode->declaration,
            ),
            StructDeclarationNode::class =>
                $this->createStructTypeFromStructDeclarationNode($exportNode->declaration)
        };
    }

    private function createStructTypeFromStructDeclarationNode(
        StructDeclarationNode $structDeclarationNode
    ): StructType {
        return new StructType(
            name: $structDeclarationNode->name->value,
            properties: new Properties(
                ...array_map(
                    fn (PropertyDeclarationNode $propertyDeclarationNode) =>
                        new Property(
                            name: $propertyDeclarationNode->name->value,
                            type: new TypeReference(
                                names: $propertyDeclarationNode->type->names->toTypeNames(),
                                isOptional: $propertyDeclarationNode->type->isOptional,
                                isArray: $propertyDeclarationNode->type->isArray
                            )
                        ),
                    $structDeclarationNode->properties->items
                )
            )
        );
    }
}
