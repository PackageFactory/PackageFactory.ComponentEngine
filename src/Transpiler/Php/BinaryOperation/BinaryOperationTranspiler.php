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

namespace PackageFactory\ComponentEngine\Transpiler\Php\BinaryOperation;

use PackageFactory\ComponentEngine\Definition\BinaryOperator;
use PackageFactory\ComponentEngine\Parser\Ast\BinaryOperationNode;
use PackageFactory\ComponentEngine\Transpiler\Php\Expression\ExpressionTranspiler;

final class BinaryOperationTranspiler
{
    private function transpileBinaryOperator(BinaryOperator $binaryOperator): string
    {
        return match ($binaryOperator) {
            BinaryOperator::AND => '&&',
            BinaryOperator::OR => '||',
            BinaryOperator::PLUS => '+',
            BinaryOperator::MINUS => '-',
            BinaryOperator::MULTIPLY_BY => '*',
            BinaryOperator::DIVIDE_BY => '/',
            BinaryOperator::MODULO => '%',
            BinaryOperator::EQUAL => '===',
            BinaryOperator::NOT_EQUAL => '!==',
            BinaryOperator::GREATER_THAN => '>',
            BinaryOperator::GREATER_THAN_OR_EQUAL => '>=',
            BinaryOperator::LESS_THAN => '<',
            BinaryOperator::LESS_THAN_OR_EQUAL => '<='
        };
    }

    public function transpile(BinaryOperationNode $binaryOperationNode): string
    {
        $expressionTranspiler = new ExpressionTranspiler(
            shouldAddQuotesIfNecessary: true
        );

        $result = $expressionTranspiler->transpile($binaryOperationNode->operands->first);
        $operator = sprintf(' %s ', $this->transpileBinaryOperator($binaryOperationNode->operator));

        foreach ($binaryOperationNode->operands->rest as $operandNode) {
            $result .= $operator;
            $result .= $expressionTranspiler->transpile($operandNode);
        }

        return sprintf('(%s)', $result);
    }
}
