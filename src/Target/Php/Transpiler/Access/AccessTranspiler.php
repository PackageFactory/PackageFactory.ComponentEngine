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

namespace PackageFactory\ComponentEngine\Target\Php\Transpiler\Access;

use PackageFactory\ComponentEngine\Definition\AccessType;
use PackageFactory\ComponentEngine\Parser\Ast\AccessNode;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\Expression\ExpressionTranspiler;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\Expression\ExpressionTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumStaticType;

final class AccessTranspiler
{
    public function __construct(private readonly ScopeInterface $scope)
    {
    }

    public function transpile(AccessNode $accessNode): string
    {
        $expressionTranspiler = new ExpressionTranspiler(
            scope: $this->scope
        );
        $expressionTypeResolver = new ExpressionTypeResolver(
            scope: $this->scope
        );
        $typeOfRoot = $expressionTypeResolver->resolveTypeOf($accessNode->root);
        $result = $expressionTranspiler->transpile($accessNode->root);

        $isFirstElement = true;
        foreach ($accessNode->chain->items as $accessChainNode) {
            if ($typeOfRoot instanceof EnumStaticType && $isFirstElement) {
                $result .= '::';
            } elseif ($accessChainNode->accessType === AccessType::OPTIONAL) {
                $result .= '?->';
            } else {
                $result .= '->';
            }
            $result .= $accessChainNode->accessor->value;
            $isFirstElement = false;
        }

        return $result;
    }
}
