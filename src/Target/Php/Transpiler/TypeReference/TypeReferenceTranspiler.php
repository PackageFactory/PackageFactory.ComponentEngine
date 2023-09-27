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

namespace PackageFactory\ComponentEngine\Target\Php\Transpiler\TypeReference;

use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeReferenceNode;
use PackageFactory\ComponentEngine\TypeSystem\AtomicTypeInterface;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\Type\BooleanType\BooleanType;
use PackageFactory\ComponentEngine\TypeSystem\Type\ComponentType\ComponentType;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumStaticType;
use PackageFactory\ComponentEngine\TypeSystem\Type\IntegerType\IntegerType;
use PackageFactory\ComponentEngine\TypeSystem\Type\SlotType\SlotType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StructType\StructType;
use PackageFactory\ComponentEngine\TypeSystem\Type\UnionType\UnionType;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

final class TypeReferenceTranspiler
{
    public function __construct(
        private readonly ScopeInterface $scope,
        private readonly TypeReferenceStrategyInterface $strategy
    ) {
    }

    public function transpile(TypeReferenceNode $typeReferenceNode): string
    {
        $type = $this->getTypeFromTypeReferenceNode($typeReferenceNode);
        $phpTypeReference = $this->transpileTypeToPHPTypeReference($type, $typeReferenceNode);

        if ($typeReferenceNode->isOptional) {
            if (str_contains($phpTypeReference, '|')) {
                $phpTypeReference = 'null|' . $phpTypeReference;
            } else {
                $phpTypeReference = '?' . $phpTypeReference;
            }
        }

        return $phpTypeReference;
    }

    private function getTypeFromTypeReferenceNode(TypeReferenceNode $typeReferenceNode): TypeInterface
    {
        $types = array_map(
            fn (TypeName $typeName) => $this->scope->getType($typeName),
            $typeReferenceNode->names->toTypeNames()->items
        );

        return UnionType::of(...$types);
    }

    private function transpileTypeToPHPTypeReference(TypeInterface $type, TypeReferenceNode $typeReferenceNode): string
    {
        if ($type instanceof UnionType) {
            return join('|', array_map(
                fn (TypeInterface $type) => $this->transpileTypeToPHPTypeReference($type, $typeReferenceNode),
                $type->members
            ));
        }

        if (!($type instanceof AtomicTypeInterface)) {
            throw new \Exception('@TODO: Cannot transpile type ' . $type::class);
        }

        return match ($type::class) {
            IntegerType::class => 'int|float',
            StringType::class => 'string',
            BooleanType::class => 'bool',
            SlotType::class => $this->strategy->getPhpTypeReferenceForSlotType($type, $typeReferenceNode),
            ComponentType::class => $this->strategy->getPhpTypeReferenceForComponentType($type, $typeReferenceNode),
            EnumStaticType::class => $this->strategy->getPhpTypeReferenceForEnumType($type, $typeReferenceNode),
            StructType::class => $this->strategy->getPhpTypeReferenceForStructType($type, $typeReferenceNode),
            default => $this->strategy->getPhpTypeReferenceForCustomType($type, $typeReferenceNode)
        };
    }
}
