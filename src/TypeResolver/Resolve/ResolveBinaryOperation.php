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

namespace PackageFactory\ComponentEngine\TypeResolver\Resolve;

use PackageFactory\ComponentEngine\Parser\Ast\BinaryOperationNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\BinaryOperand;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\BinaryOperands;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\BinaryOperation;

trait ResolveBinaryOperation
{
    abstract private function resolveBinaryOperand(
        ExpressionNode $operandNode
    ): BinaryOperand;

    private function resolveBinaryOperation(
        BinaryOperationNode $binaryOperationNode
    ): BinaryOperation {
        $firstOperand = $this->resolveBinaryOperand($binaryOperationNode->operands->first);
        $type = $firstOperand->type;

        $operands = [$firstOperand];
        foreach ($binaryOperationNode->operands->rest as $operandNode) {
            $operand = $this->resolveBinaryOperand($operandNode);
            $type = $type->binaryOperation(
                $binaryOperationNode->operator, 
                $operand->type
            );
            $operands[] = $operand;
        }

        return new BinaryOperation(
            operator: $binaryOperationNode->operator,
            operands: new BinaryOperands(...$operands),
            type: $type
        );
    }
}
