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

use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNodes;
use PackageFactory\ComponentEngine\Parser\Ast\FunctionCallNode;
use PackageFactory\ComponentEngine\Type\FunctionType;
use PackageFactory\ComponentEngine\Type\MethodType;
use PackageFactory\ComponentEngine\TypeResolver\Scope\Scope;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\ActualParameters;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\Expression;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\FunctionCall;
use PackageFactory\ComponentEngine\TypeResolver\TypeInferrer;

trait ResolveFunctionCall
{
    private readonly Scope $scope;

    abstract private function resolveExpression(
        ExpressionNode $expressionNode
    ): Expression;

    abstract private function resolveActualParameters(
        FunctionType $callerType,
        ExpressionNodes $actualParameterNodes
    ): ActualParameters;

    private function resolveFunctionCall(
        FunctionCallNode $functionCallNode
    ): FunctionCall {
        $typeInferrer = new TypeInferrer($this);

        $caller = $this->resolveExpression($functionCallNode->callerExpression);
        $caller = $caller->withType(
            match ($caller->type::class) {
                FunctionType::class => $typeInferrer->inferFunctionType(
                    $typeInferrer->inferFunctionType(
                        $caller->type,
                        $functionCallNode
                    ),
                    $functionCallNode
                ),
                MethodType::class => $typeInferrer->inferMethodType(
                    $typeInferrer->inferMethodType(
                        $caller->type,
                        $functionCallNode
                    ),
                    $functionCallNode
                ),
                default => throw new \Exception('@TODO: Type not callable ' . $caller->type)
            }
        );
        /** @var FunctionType|MethodType $callableType */
        $callableType = $caller->type;
        $actualParameters = $this->resolveActualParameters(
            $callableType->getFunctionType(),
            $functionCallNode->actualParameters
        );

        return new FunctionCall(
            caller: $caller,
            actualParameters: $actualParameters,
            type: $callableType->getReturnType()
        );
    }
}
