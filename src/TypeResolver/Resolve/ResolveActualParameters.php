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

use PackageFactory\ComponentEngine\Parser\Ast\ArrowFunctionNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNodes;
use PackageFactory\ComponentEngine\Type\FunctionType;
use PackageFactory\ComponentEngine\Type\Type;
use PackageFactory\ComponentEngine\TypeResolver\Scope\BlockScope;
use PackageFactory\ComponentEngine\TypeResolver\Scope\Scope;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\ActualParameter;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\ActualParameters;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\ArrowFunction;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\Expression;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\Identifier;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\ParameterDeclaration;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\ParameterDeclarations;

trait ResolveActualParameters
{
    abstract private function withPushedScope(Scope $scope): self;

    abstract private function resolveExpression(
        ExpressionNode $expressionNode
    ): Expression;

    private function resolveActualParameters(
        FunctionType $callerType,
        ExpressionNodes $actualParameterNodes
    ): ActualParameters {
        $items = [];
        foreach ($actualParameterNodes->items as $index => $actualParameterNode) {
            $items[] = $this->resolveActualParameter(
                $callerType->parameterTypes->items[$index],
                $actualParameterNode
            );
        }

        return new ActualParameters(...$items);
    }

    private function resolveActualParameter(
        Type $contract,
        ExpressionNode $actualParameterNode
    ): ActualParameter {
        $value = match ($actualParameterNode->root::class) {
            ArrowFunctionNode::class => match ($contract::class) {
                FunctionType::class =>  $this->resolveActualArrowFunction($contract, $actualParameterNode->root),
                default => throw new \Exception('@TODO: ' . $contract . ' is not a valid contract for an arrow function')
            },
            default => $this->resolveExpression($actualParameterNode)
        };

        return new ActualParameter(
            value: $value,
            type: $value->type
        );
    }

    private function resolveActualArrowFunction(
        FunctionType $contract, 
        ArrowFunctionNode $arrowFunctionNode
    ): Expression {
        $parameterDeclarations = [];
        foreach (array_values($arrowFunctionNode->parameterDeclarations->items) as $index => $parameterDeclarationNode) {
            $parameterDeclarations[] = new ParameterDeclaration(
                name: new Identifier($parameterDeclarationNode->name),
                type: $contract->parameterTypes->items[$index]
            );
        }

        $parameterDeclarations = new ParameterDeclarations(...$parameterDeclarations);
        $arrowFunction = new ArrowFunction(
            parameterDeclarations: $parameterDeclarations,
            returnExpression: $this->withPushedScope(
                BlockScope::fromRecordType($parameterDeclarations->toRecordType())
            )->resolveExpression($arrowFunctionNode->returnExpression),
            type: $contract
        );

        return new Expression(
            root: $arrowFunction,
            type: $contract
        );
    }
}
