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

namespace PackageFactory\ComponentEngine\TypeSystem\Resolver\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\BinaryOperationNode;
use PackageFactory\ComponentEngine\Parser\Ast\BooleanLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\IdentifierNode;
use PackageFactory\ComponentEngine\Parser\Ast\MatchNode;
use PackageFactory\ComponentEngine\Parser\Ast\NumberLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\StringLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\TagNode;
use PackageFactory\ComponentEngine\Parser\Ast\TernaryOperationNode;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\BinaryOperation\BinaryOperationTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\BooleanLiteral\BooleanLiteralTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\Identifier\IdentifierTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\Match\MatchTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\NumberLiteral\NumberLiteralTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\StringLiteral\StringLiteralTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\Tag\TagTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\TernaryOperation\TernaryOperationTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

final class ExpressionTypeResolver
{
    public function __construct(
        private readonly ScopeInterface $scope
    ) {
    }

    public function resolveTypeOf(ExpressionNode $expressionNode): TypeInterface
    {
        $rootTypeResolver = match ($expressionNode->root::class) {
            BinaryOperationNode::class => new BinaryOperationTypeResolver(
                scope: $this->scope
            ),
            BooleanLiteralNode::class => new BooleanLiteralTypeResolver(),
            IdentifierNode::class => new IdentifierTypeResolver(
                scope: $this->scope
            ),
            MatchNode::class => new MatchTypeResolver(
                scope: $this->scope
            ),
            NumberLiteralNode::class => new NumberLiteralTypeResolver(),
            StringLiteralNode::class => new StringLiteralTypeResolver(),
            TagNode::class => new TagTypeResolver(),
            TernaryOperationNode::class => new TernaryOperationTypeResolver(
                scope: $this->scope
            ),
            default => throw new \Exception('@TODO: Resolve type of ' . $expressionNode->root::class)
        };

        return $rootTypeResolver->resolveTypeOf($expressionNode->root);
    }
}
