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

namespace PackageFactory\ComponentEngine\TypeSystem\Resolver\Access;

use PackageFactory\ComponentEngine\TypeSystem\Resolver\Expression\ExpressionTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumInstanceType;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumStaticType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StructType\StructType;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;
use PackageFactory\ComponentEngine\Language\AST\Node\Access\AccessKeyNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Access\AccessNode;

final class AccessTypeResolver
{
    public function __construct(
        private readonly ScopeInterface $scope
    ) {
    }

    public function resolveTypeOf(AccessNode $accessNode): TypeInterface
    {
        $expressionTypeResolver = new ExpressionTypeResolver(scope: $this->scope);
        $parentType = $expressionTypeResolver->resolveTypeOf($accessNode->parent);

        return match ($parentType::class) {
            EnumStaticType::class => $this->resolveEnumInstanceMemberType($parentType, $accessNode->key),
            StructType::class => $this->resolveStructPropertyType($parentType, $accessNode->key),
            default => throw new \Exception('@TODO Error: Cannot access on type ' . $parentType::class)
        };
    }

    private function resolveEnumInstanceMemberType(EnumStaticType $enumType, AccessKeyNode $keyNode): EnumInstanceType
    {
        return $enumType->getMemberType($keyNode->value->toEnumMemberName());
    }

    private function resolveStructPropertyType(StructType $structType, AccessKeyNode $keyNode): TypeInterface
    {
        return $structType->getTypeOfProperty($keyNode->value)->toType($this->scope);
    }
}
