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

namespace PackageFactory\ComponentEngine\TypeSystem\Resolver\BinaryOperation;

use PackageFactory\ComponentEngine\Definition\BinaryOperator;
use PackageFactory\ComponentEngine\Parser\Ast\BinaryOperandNodes;
use PackageFactory\ComponentEngine\Parser\Ast\BinaryOperationNode;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\Expression\ExpressionTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\Type\BooleanType\BooleanType;
use PackageFactory\ComponentEngine\TypeSystem\Type\NumberType\NumberType;
use PackageFactory\ComponentEngine\TypeSystem\Type\UnionType\UnionType;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

final class BinaryOperationTypeResolver
{
    public function __construct(
        private readonly ScopeInterface $scope
    ) {
    }

    public function resolveTypeOf(BinaryOperationNode $binaryOperationNode): TypeInterface
    {
        return match ($binaryOperationNode->operator) {
            BinaryOperator::AND,
            BinaryOperator::OR => $this->resolveTypeOfBooleanOperation($binaryOperationNode->operands),

            BinaryOperator::PLUS,
            BinaryOperator::MINUS,
            BinaryOperator::MULTIPLY_BY,
            BinaryOperator::DIVIDE_BY,
            BinaryOperator::MODULO => $this->resolveTypeOfArithmeticOperation($binaryOperationNode->operands),

            BinaryOperator::EQUAL,
            BinaryOperator::NOT_EQUAL,
            BinaryOperator::GREATER_THAN,
            BinaryOperator::GREATER_THAN_OR_EQUAL,
            BinaryOperator::LESS_THAN,
            BinaryOperator::LESS_THAN_OR_EQUAL => BooleanType::get()
        };
    }

    private function resolveTypeOfBooleanOperation(BinaryOperandNodes $operandNodes): TypeInterface
    {
        $expressionTypeResolver = new ExpressionTypeResolver(
            scope: $this->scope
        );
        $operandTypes = [];

        foreach ($operandNodes as $operandNode) {
            $operandTypes[] = $expressionTypeResolver->resolveTypeOf($operandNode);
        }

        return UnionType::of(...$operandTypes);
    }

    private function resolveTypeOfArithmeticOperation(BinaryOperandNodes $operandNodes): TypeInterface
    {
        $expressionTypeResolver = new ExpressionTypeResolver(
            scope: $this->scope
        );
        $numberType = NumberType::get();

        foreach ($operandNodes as $operandNode) {
            $typeOfOperandNode = $expressionTypeResolver->resolveTypeOf($operandNode);
            $typeOfOperandNodeIsNumberType = $typeOfOperandNode->is($numberType);

            if (!$typeOfOperandNodeIsNumberType) {
                throw new \Exception('@TODO: Operand must be of type number');
            }
        }

        return $numberType;
    }
}
