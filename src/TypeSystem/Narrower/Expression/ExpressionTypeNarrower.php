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

namespace PackageFactory\ComponentEngine\TypeSystem\Narrower\Expression;

use PackageFactory\ComponentEngine\Definition\BinaryOperator;
use PackageFactory\ComponentEngine\Parser\Ast\BinaryOperationNode;
use PackageFactory\ComponentEngine\Parser\Ast\BooleanLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\IdentifierNode;
use PackageFactory\ComponentEngine\Parser\Ast\UnaryOperationNode;
use PackageFactory\ComponentEngine\TypeSystem\Narrower\NarrowedTypes;
use PackageFactory\ComponentEngine\TypeSystem\Narrower\Truthiness;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\Expression\ExpressionTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\Type\NullType\NullType;

/**
 * This class handles the analysis of identifier types that are used in a condition
 * and based on the requested branch: truthy or falsy, will predict the types a variable will have in the respective branch
 * so it matches the expected runtime behaviour
 *
 * For example given this expression: `nullableString ? nullableString : "fallback"` based on the condition `nullableString`
 * it will infer that in the truthy context nullableString is a string while in the falsy context it will infer that it is null.
 * In the above case the ternary expression will resolve to a string.
 *
 * The structure is partially inspired by phpstan
 * https://github.com/phpstan/phpstan-src/blob/07bb4aa2d5e39dafa78f56c5df132c763c2d1b67/src/Analyser/TypeSpecifier.php#L111
 */
class ExpressionTypeNarrower
{
    private function __construct(
        private readonly ScopeInterface $scope,
        private readonly Truthiness $assumedTruthiness
    ) {
    }

    public static function forTruthy(ScopeInterface $scope): self
    {
        return new self($scope, Truthiness::TRUTHY);
    }

    public static function forFalsy(ScopeInterface $scope): self
    {
        return new self($scope, Truthiness::FALSY);
    }

    public function narrowTypesOfSymbolsIn(ExpressionNode $expressionNode): NarrowedTypes
    {
        if ($expressionNode->root instanceof IdentifierNode) {
            $type = $this->scope->lookupTypeFor($expressionNode->root->value);
            if (!$type) {
                return NarrowedTypes::empty();
            }
            // case `nullableString ? "nullableString is not null" : "nullableString is null"`
            return NarrowedTypes::fromEntry($expressionNode->root->value, $this->assumedTruthiness->narrowType($type));
        }

        if (($binaryOperationNode = $expressionNode->root) instanceof BinaryOperationNode) {
            $right = $binaryOperationNode->right;
            $left = $binaryOperationNode->left;

            if (
                (($boolean = $right->root) instanceof BooleanLiteralNode
                    && $other = $left // @phpstan-ignore-line
                ) || (($boolean = $left->root) instanceof BooleanLiteralNode
                    && $other = $right // @phpstan-ignore-line
                )
            ) {
                switch ($binaryOperationNode->operator) {
                    case BinaryOperator::AND:
                        if ($boolean->value && $this->assumedTruthiness === Truthiness::TRUTHY) {
                            return $this->narrowTypesOfSymbolsIn($other);
                        }
                        break;
                    case BinaryOperator::EQUAL:
                    case BinaryOperator::NOT_EQUAL:
                        $contextBasedOnOperator = $this->assumedTruthiness->basedOnBinaryOperator($binaryOperationNode->operator);
                        assert($contextBasedOnOperator !== null);

                        if ($other->root instanceof IdentifierNode) {
                            return NarrowedTypes::empty();
                        }

                        $subNarrower = new self(
                            $this->scope,
                            $boolean->value ? $contextBasedOnOperator : $contextBasedOnOperator->negate()
                        );
                        return $subNarrower->narrowTypesOfSymbolsIn($other);
                }

                return NarrowedTypes::empty();
            }

            $expressionTypeResolver = (new ExpressionTypeResolver($this->scope));
            if (
                ($expressionTypeResolver->resolveTypeOf($right)->is(NullType::get())
                    && $other = $left // @phpstan-ignore-line
                ) || ($expressionTypeResolver->resolveTypeOf($left)->is(NullType::get())
                    && $other = $right // @phpstan-ignore-line
                )
            ) {
                if (!$other->root instanceof IdentifierNode) {
                    return NarrowedTypes::empty();
                }
                $type = $this->scope->lookupTypeFor($other->root->value);
                if (!$type) {
                    return NarrowedTypes::empty();
                }

                if (!$contextBasedOnOperator = $this->assumedTruthiness->basedOnBinaryOperator($binaryOperationNode->operator)) {
                    return NarrowedTypes::empty();
                }

                return NarrowedTypes::fromEntry(
                    $other->root->value,
                    $contextBasedOnOperator->negate()->narrowType($type)
                );
            }
        }

        if (($unaryOperationNode = $expressionNode->root) instanceof UnaryOperationNode) {
            $subNarrower = new self(
                $this->scope,
                $this->assumedTruthiness->negate()
            );
            return $subNarrower->narrowTypesOfSymbolsIn($unaryOperationNode->argument);
        }

        return NarrowedTypes::empty();
    }
}
