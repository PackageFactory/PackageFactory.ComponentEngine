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

namespace PackageFactory\ComponentEngine\TypeSystem\Resolver\ExpressionTypeResolver;

use PackageFactory\ComponentEngine\Parser\Ast\BinaryOperationNode;
use PackageFactory\ComponentEngine\Parser\Ast\BooleanLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\IdentifierNode;
use PackageFactory\ComponentEngine\Parser\Ast\NumberLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\StringLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\TagNode;
use PackageFactory\ComponentEngine\Parser\Ast\TernaryOperationNode;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\BinaryOperationTypeResolver\BinaryOperationTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\BooleanLiteralTypeResolver\BooleanLiteralTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\IdentifierTypeResolver\IdentifierTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\NumberLiteralTypeResolver\NumberLiteralTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\StringLiteralTypeResolver\StringLiteralTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\TagTypeResolver\TagTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\TernaryOperationTypeResolver\TernaryOperationTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

final class ExpressionTypeResolver
{
    public function __construct(
        private readonly ScopeInterface $scope,
        private ?BinaryOperationTypeResolver $binaryOperationTypeResolver = null,
        private ?BooleanLiteralTypeResolver $booleanLiteralTypeResolver = null,
        private ?IdentifierTypeResolver $identifierTypeResolver = null,
        private ?NumberLiteralTypeResolver $numberLiteralTypeResolver = null,
        private ?StringLiteralTypeResolver $stringLiteralTypeResolver = null,
        private ?TagTypeResolver $tagTypeResolver = null,
        private ?TernaryOperationTypeResolver $ternaryOperationTypeResolver = null
    ) {
    }

    private function getBinaryOperationTypeResolver(): BinaryOperationTypeResolver
    {
        return $this->binaryOperationTypeResolver ??= new BinaryOperationTypeResolver(
            expressionTypeResolver: $this
        );
    }

    private function getBooleanLiteralTypeResolver(): BooleanLiteralTypeResolver
    {
        return $this->booleanLiteralTypeResolver ??= new BooleanLiteralTypeResolver();
    }

    private function getIdentifierTypeResolver(): IdentifierTypeResolver
    {
        return $this->identifierTypeResolver ??= new IdentifierTypeResolver(
            scope: $this->scope
        );
    }

    private function getNumberLiteralTypeResolver(): NumberLiteralTypeResolver
    {
        return $this->numberLiteralTypeResolver ??= new NumberLiteralTypeResolver();
    }

    private function getStringLiteralTypeResolver(): StringLiteralTypeResolver
    {
        return $this->stringLiteralTypeResolver ??= new StringLiteralTypeResolver();
    }

    private function getTagTypeResolver(): TagTypeResolver
    {
        return $this->tagTypeResolver ??= new TagTypeResolver();
    }

    private function getTernaryOperationTypeResolver(): TernaryOperationTypeResolver
    {
        return $this->ternaryOperationTypeResolver ??= new TernaryOperationTypeResolver(
            expressionTypeResolver: $this
        );
    }

    public function resolveTypeOf(ExpressionNode $expressionNode): TypeInterface
    {
        $rootTypeResolver = match ($expressionNode->root::class) {
            BinaryOperationNode::class => $this->getBinaryOperationTypeResolver(),
            BooleanLiteralNode::class => $this->getBooleanLiteralTypeResolver(),
            IdentifierNode::class => $this->getIdentifierTypeResolver(),
            NumberLiteralNode::class => $this->getNumberLiteralTypeResolver(),
            StringLiteralNode::class => $this->getStringLiteralTypeResolver(),
            TagNode::class => $this->getTagTypeResolver(),
            TernaryOperationNode::class => $this->getTernaryOperationTypeResolver(),
            default => throw new \Exception('@TODO: Resolve type of ' . $expressionNode->root::class)
        };

        return $rootTypeResolver->resolveTypeOf($expressionNode->root);
    }
}
