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

namespace PackageFactory\ComponentEngine\Target\Php\Transpiler\UnaryOperation;

use PackageFactory\ComponentEngine\Definition\UnaryOperator;
use PackageFactory\ComponentEngine\Parser\Ast\UnaryOperationNode;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\Expression\ExpressionTranspiler;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;

final class UnaryOperationTranspiler
{
    public function __construct(private readonly ScopeInterface $scope)
    {
    }

    private function transpileUnaryOperator(UnaryOperator $operator): string
    {
        return match ($operator) {
            UnaryOperator::NOT => '!'
        };
    }

    public function transpile(UnaryOperationNode $unaryOperationNode): string
    {
        $expressionTranspiler = new ExpressionTranspiler(
            scope: $this->scope,
            shouldAddQuotesIfNecessary: true
        );

        $operator = $this->transpileUnaryOperator($unaryOperationNode->operator);
        $argument = $expressionTranspiler->transpile($unaryOperationNode->argument);

        return sprintf('(%s%s)', $operator, $argument);
    }
}
