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

namespace PackageFactory\ComponentEngine\TypeSystem\Narrower;

use PackageFactory\ComponentEngine\Definition\BinaryOperator;
use PackageFactory\ComponentEngine\Parser\Ast\BinaryOperationNode;
use PackageFactory\ComponentEngine\Parser\Ast\BooleanLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\IdentifierNode;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\Expression\ExpressionTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\Type\NullType\NullType;

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
class ExpressionTypeNarrower
{
    public function __construct(
        private readonly ScopeInterface $scope
    ) {
    }

    public function narrowTypesOfSymbolsIn(ExpressionNode $expressionNode, TypeNarrowerContext $context): NarrowedTypes
    {
        if ($expressionNode->root instanceof IdentifierNode) {
            $type = $this->scope->lookupTypeFor($expressionNode->root->value);
            if (!$type) {
                return NarrowedTypes::empty();
            }
            // case `nullableString ? "nullableString is not null" : "nullableString is null"`
            return NarrowedTypes::fromEntry($expressionNode->root->value, $context->narrowType($type));
        }

        if (($binaryOperationNode = $expressionNode->root) instanceof BinaryOperationNode) {
            // todo we currently only work with two operands
            if (count($binaryOperationNode->operands->rest) !== 1) {
                return NarrowedTypes::empty();
            }
            $first = $binaryOperationNode->operands->first;
            $second = $binaryOperationNode->operands->rest[0];

            if (
                (($boolean = $first->root) instanceof BooleanLiteralNode
                    && $other = $second // @phpstan-ignore-line
                ) || (($boolean = $second->root) instanceof BooleanLiteralNode
                    && $other = $first // @phpstan-ignore-line
                )
            ) {
                switch ($binaryOperationNode->operator) {
                    case BinaryOperator::AND:
                        if ($boolean->value && $context === TypeNarrowerContext::TRUTHY) {
                            return $this->narrowTypesOfSymbolsIn($other, $context);
                        }
                        break;
                    case BinaryOperator::EQUAL:
                    case BinaryOperator::NOT_EQUAL:
                        $contextBasedOnOperator = $context->basedOnBinaryOperator($binaryOperationNode->operator);
                        assert($contextBasedOnOperator !== null);

                        if ($other->root instanceof IdentifierNode) {
                            return NarrowedTypes::empty();
                        }

                        return $this->narrowTypesOfSymbolsIn(
                            $other,
                            $boolean->value ? $contextBasedOnOperator : $contextBasedOnOperator->negate()
                        );
                }

                return NarrowedTypes::empty();
            }

            $expressionTypeResolver = (new ExpressionTypeResolver($this->scope));
            if (
                ($expressionTypeResolver->resolveTypeOf($first)->is(NullType::get())
                    && $other = $second // @phpstan-ignore-line
                ) || ($expressionTypeResolver->resolveTypeOf($second)->is(NullType::get())
                    && $other = $first // @phpstan-ignore-line
                )
            ) {
                if (!$other->root instanceof IdentifierNode) {
                    return NarrowedTypes::empty();
                }
                $type = $this->scope->lookupTypeFor($other->root->value);
                if (!$type) {
                    return NarrowedTypes::empty();
                }

                if (!$contextBasedOnOperator = $context->basedOnBinaryOperator($binaryOperationNode->operator)) {
                    return NarrowedTypes::empty();
                }

                return NarrowedTypes::fromEntry(
                    $other->root->value,
                    $contextBasedOnOperator->negate()->narrowType($type)
                );
            }
        }

        return NarrowedTypes::empty();
    }
}
