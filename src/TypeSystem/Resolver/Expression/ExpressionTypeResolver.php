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

use PackageFactory\ComponentEngine\Parser\Ast\AccessNode;
use PackageFactory\ComponentEngine\Parser\Ast\BinaryOperationNode;
use PackageFactory\ComponentEngine\Parser\Ast\BooleanLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\IdentifierNode;
use PackageFactory\ComponentEngine\Parser\Ast\MatchNode;
use PackageFactory\ComponentEngine\Parser\Ast\NullLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\IntegerLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\StringLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\TagNode;
use PackageFactory\ComponentEngine\Parser\Ast\TemplateLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\TernaryOperationNode;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\Access\AccessTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\BinaryOperation\BinaryOperationTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\BooleanLiteral\BooleanLiteralTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\Identifier\IdentifierTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\Match\MatchTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\NullLiteral\NullLiteralTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\IntegerLiteral\IntegerLiteralTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\StringLiteral\StringLiteralTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\Tag\TagTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\TemplateLiteral\TemplateLiteralTypeResolver;
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
        $rootNode = $expressionNode->root;
        return match ($rootNode::class) {
            BinaryOperationNode::class => (new BinaryOperationTypeResolver(
                scope: $this->scope
            ))->resolveTypeOf($rootNode),
            BooleanLiteralNode::class => (new BooleanLiteralTypeResolver())
                ->resolveTypeOf($rootNode),
            IdentifierNode::class => (new IdentifierTypeResolver(
                scope: $this->scope
            ))->resolveTypeOf($rootNode),
            MatchNode::class => (new MatchTypeResolver(
                scope: $this->scope
            ))->resolveTypeOf($rootNode),
            NullLiteralNode::class => (new NullLiteralTypeResolver())
                ->resolveTypeOf($rootNode),
            IntegerLiteralNode::class => (new IntegerLiteralTypeResolver())
                ->resolveTypeOf($rootNode),
            StringLiteralNode::class => (new StringLiteralTypeResolver())
                ->resolveTypeOf($rootNode),
            TagNode::class => (new TagTypeResolver())
                ->resolveTypeOf($rootNode),
            TemplateLiteralNode::class => (new TemplateLiteralTypeResolver())
                ->resolveTypeOf($rootNode),
            TernaryOperationNode::class => (new TernaryOperationTypeResolver(
                scope: $this->scope
            ))->resolveTypeOf($rootNode),
            AccessNode::class => (new AccessTypeResolver(
                scope: $this->scope
            ))->resolveTypeOf($rootNode),
            default => throw new \Exception('@TODO: Resolve type of ' . $expressionNode->root::class)
        };
    }
}
