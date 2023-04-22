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

namespace PackageFactory\ComponentEngine\TypeSystem\Scope\ShallowScope;

use PackageFactory\ComponentEngine\Definition\BinaryOperator;
use PackageFactory\ComponentEngine\Parser\Ast\BinaryOperationNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\IdentifierNode;
use PackageFactory\ComponentEngine\Parser\Ast\NullLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\TypeReferenceNode;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\Type\NullType\NullType;
use PackageFactory\ComponentEngine\TypeSystem\Type\UnionType\UnionType;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

final class TernaryBranchScope implements ScopeInterface
{
    private function __construct(
        private readonly ExpressionNode $conditionNode,
        private readonly bool $isBranchTrue,
        private readonly ScopeInterface $parentScope
    ) {
    }

    public static function forTrueBranch(ExpressionNode $conditionNode, ScopeInterface $parentScope): self
    {
        return new self(conditionNode: $conditionNode, isBranchTrue: true, parentScope: $parentScope);
    }

    public static function forFalseBranch(ExpressionNode $conditionNode, ScopeInterface $parentScope): self
    {
        return new self(conditionNode: $conditionNode, isBranchTrue: false, parentScope: $parentScope);
    }

    public function lookupTypeFor(string $name): ?TypeInterface
    {
        $type = $this->parentScope->lookupTypeFor($name);

        if (!$type instanceof UnionType || !$type->containsNull()) {
            return $type;
        }

        if ($this->conditionNode->root instanceof IdentifierNode && $this->conditionNode->root->value === $name) {
            // case `nullableString ? "nullableString is not null" : "nullableString is null"`
            return $this->isBranchTrue ? $type->withoutNull() : NullType::get();
        }

        if (($binaryOperationNode = $this->conditionNode->root) instanceof BinaryOperationNode) {
            // cases
            // `nullableString === null ? "nullableString is null" : "nullableString is not null"`
            // `nullableString !== null ? "nullableString is not null" : "nullableString is null"`
            if (count($binaryOperationNode->operands->rest) !== 1) {
                return $type;
            }
            $first = $binaryOperationNode->operands->first;
            $second = $binaryOperationNode->operands->rest[0];
            // case `nullableString === null`
            $isFirstToBeLookedUpName = $first->root instanceof IdentifierNode && $first->root->value === $name;
            $isFirstComparedToNull = $isFirstToBeLookedUpName && $second->root instanceof NullLiteralNode;
            // yodas case `null === nullableString`
            $isSecondToBeLookedUpName = $second->root instanceof IdentifierNode && $second->root->value === $name;
            $isSecondComparedToNull = $first->root instanceof NullLiteralNode && $isSecondToBeLookedUpName;
            if (!$isFirstComparedToNull && !$isSecondComparedToNull) {
                return $type;
            }
            if ($binaryOperationNode->operator === BinaryOperator::EQUAL) {
                return $this->isBranchTrue ? NullType::get() : $type->withoutNull();
            }
            if ($binaryOperationNode->operator === BinaryOperator::NOT_EQUAL) {
                return $this->isBranchTrue ? $type->withoutNull() : NullType::get();
            }
        }

        return $type;
    }

    public function resolveTypeReference(TypeReferenceNode $typeReferenceNode): TypeInterface
    {
        return $this->parentScope->resolveTypeReference($typeReferenceNode);
    }
}
