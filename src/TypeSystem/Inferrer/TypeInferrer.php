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

namespace PackageFactory\ComponentEngine\TypeSystem\Inferrer;

use PackageFactory\ComponentEngine\Definition\BinaryOperator;
use PackageFactory\ComponentEngine\Parser\Ast\BinaryOperationNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\IdentifierNode;
use PackageFactory\ComponentEngine\Parser\Ast\NullLiteralNode;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\Type\NullType\NullType;
use PackageFactory\ComponentEngine\TypeSystem\Type\UnionType\UnionType;

/**
 * This class handles the analysis of identifier types that are used in a condition
 * and based on the requested branch: truthy or falsy, will predict the types a variable will have in the respective branch
 * so it matches the expected runtime behaviour
 *
 * For example given this expression: `nullableString ? "nullableString is not null" : "nullableString is null"` based on the condition `nullableString`
 * It will infer that in the truthy context nullableString is a string while in the falsy context it will infer that it is a null
 *
 * The structure is partially inspired by phpstan
 * https://github.com/phpstan/phpstan-src/blob/07bb4aa2d5e39dafa78f56c5df132c763c2d1b67/src/Analyser/TypeSpecifier.php#L111
 */
class TypeInferrer
{
    public function __construct(
        private readonly ScopeInterface $scope
    ) {
    }

    public function inferTypesInCondition(ExpressionNode $conditionNode, TypeInferrerContext $context): InferredTypes
    {
        if ($conditionNode->root instanceof IdentifierNode) {
            $type = $this->scope->lookupTypeFor($conditionNode->root->value);
            // case `nullableString ? "nullableString is not null" : "nullableString is null"`
            if (!$type instanceof UnionType || !$type->containsNull()) {
                return new InferredTypes();
            }

            return new InferredTypes(
                ...[$conditionNode->root->value => $context->isTrue() ? $type->withoutNull() : NullType::get()]
            );
        }

        if (($binaryOperationNode = $conditionNode->root) instanceof BinaryOperationNode) {
            // cases
            // `nullableString === null ? "nullableString is null" : "nullableString is not null"`
            // `nullableString !== null ? "nullableString is not null" : "nullableString is null"`
            if (count($binaryOperationNode->operands->rest) !== 1) {
                return new InferredTypes();
            }
            $first = $binaryOperationNode->operands->first;
            $second = $binaryOperationNode->operands->rest[0];

            $comparedIdentifierValueToNull = match (true) {
                // case `nullableString === null`
                $first->root instanceof IdentifierNode && $second->root instanceof NullLiteralNode => $first->root->value,
                // yodas case `null === nullableString`
                $first->root instanceof NullLiteralNode && $second->root instanceof IdentifierNode => $second->root->value,
                default => null
            };

            if ($comparedIdentifierValueToNull === null) {
                return new InferredTypes();
            }

            $type = $this->scope->lookupTypeFor($comparedIdentifierValueToNull);
            if (!$type instanceof UnionType || !$type->containsNull()) {
                return new InferredTypes();
            }

            if ($binaryOperationNode->operator === BinaryOperator::EQUAL) {
                return new InferredTypes(
                    ...[$comparedIdentifierValueToNull => $context->isTrue() ? NullType::get() : $type->withoutNull()]
                );
            }
            if ($binaryOperationNode->operator === BinaryOperator::NOT_EQUAL) {
                return new InferredTypes(
                    ...[$comparedIdentifierValueToNull => $context->isTrue() ? $type->withoutNull() : NullType::get()]
                );
            }
        }

        return new InferredTypes();
    }
}
