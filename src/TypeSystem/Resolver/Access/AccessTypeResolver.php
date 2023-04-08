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


use PackageFactory\ComponentEngine\Parser\Ast\AccessNode;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\Expression\ExpressionTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumMemberType;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumStaticType;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StructType\StructType;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;
use PackageFactory\ComponentEngine\Definition\AccessType;

final class AccessTypeResolver
{
    public function __construct(
        private readonly ScopeInterface $scope
    ) {
    }

    public function resolveTypeOf(AccessNode $accessNode): TypeInterface
    {
        $expressionResolver = new ExpressionTypeResolver(scope: $this->scope);
        $rootType = $expressionResolver->resolveTypeOf($accessNode->root);

        return match ($rootType::class) {
            EnumType::class, EnumStaticType::class => $this->createEnumMemberType($accessNode, $rootType),
            StructType::class => throw new \Exception('@TODO: StructType Access is not implemented'),
            default => throw new \Exception('@TODO Error: Cannot access on type ' . $rootType::class)
        };
    }
    
    private function createEnumMemberType(AccessNode $accessNode, EnumType|EnumStaticType $enumType): EnumMemberType
    {
        if (!(
            count($accessNode->chain->items) === 1
            && $accessNode->chain->items[0]->accessType === AccessType::MANDATORY
        )) {
            throw new \Error('@TODO Error: Enum access malformed, only one level member access is allowed.');
        }

        $enumMemberName = $accessNode->chain->items[0]->accessor->value;
        
        return $enumType->getMemberType($enumMemberName);
    }
}
