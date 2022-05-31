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

namespace PackageFactory\ComponentEngine\TypeResolver;

use PackageFactory\ComponentEngine\Parser\Ast\ArrowFunctionNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\FunctionCallNode;
use PackageFactory\ComponentEngine\Parser\Ast\ParameterDeclarationNode;
use PackageFactory\ComponentEngine\Type\ArrayType;
use PackageFactory\ComponentEngine\Type\FunctionType;
use PackageFactory\ComponentEngine\Type\Generic\GenericType;
use PackageFactory\ComponentEngine\Type\MethodType;
use PackageFactory\ComponentEngine\Type\Record\RecordEntry;
use PackageFactory\ComponentEngine\Type\Record\RecordType;
use PackageFactory\ComponentEngine\Type\Tuple;
use PackageFactory\ComponentEngine\Type\Type;
use PackageFactory\ComponentEngine\TypeResolver\Scope\BlockScope;

final class TypeInferrer
{
    /**
     * @var \SplObjectStorage<GenericType,Type>
     */
    private \SplObjectStorage $knownTypes;

    public function __construct(
        private readonly TypeResolver $typeResolver
    ) {
        $this->knownTypes = new class extends \SplObjectStorage
        {
            public function offsetSet(mixed $object, mixed $info = null): void
            {
                if ($info instanceof GenericType) {
                    throw new \Exception('This is not allowed!');
                }

                parent::offsetSet($object, $info);
            }
        };
    }

    public function inferFunctionType(FunctionType $contract, FunctionCallNode $functionCallNode): FunctionType
    {
        $parameterTypes = [];
        foreach ($contract->parameterTypes->items as $index => $parameterType) {
            $parameterTypes[] = $this->inferTypeOfActualParameter(
                $parameterType,
                $functionCallNode->actualParameters->items[$index] ?? null
            );
        }

        $returnType = $this->inferType($contract->returnType);

        return FunctionType::create(
            Tuple::of(...$parameterTypes),
            $returnType
        );
    }

    public function inferMethodType(MethodType $contract, FunctionCallNode $functionCallNode): MethodType
    {
        return $contract->withFunctionType(
            $this->inferFunctionType(
                $contract->functionType,
                $functionCallNode
            )
        );
    }

    public function inferTypeOfActualParameter(Type $contract, ExpressionNode $expressionNode): Type
    {
        return match ($contract::class) {
            FunctionType::class => match ($expressionNode->root::class) {
                ArrowFunctionNode::class => $this->inferTypeOfArrowFunction($contract, $expressionNode->root),
                default => throw new \Exception('@TODO: ' . $contract . ' is not a contract for ' . $expressionNode->root::class)
            },
            GenericType::class => $this->knownTypes->contains($contract)
                ? $this->knownTypes[$contract]
                : $this->knownTypes[$contract] = $this->typeResolver->getTypeOfExpression($expressionNode),
            default => $contract
        };
    }

    public function inferTypeOfArrowFunction(FunctionType $contract, ArrowFunctionNode $arrowFunctionNode): FunctionType
    {
        $entries = [];
        foreach (array_values($arrowFunctionNode->parameterDeclarations->items) as $index => $parameterDeclarationNode) {
            $parameterType = $contract->parameterTypes->items[$index];
            $entries[] = RecordEntry::of(
                $parameterDeclarationNode->name,
                $this->inferParameterType($parameterType, $parameterDeclarationNode)
            );
        }

        $parameterTypes = RecordType::of(...$entries);
        $bodyScope = BlockScope::fromRecordType($parameterTypes);

        try {
            $returnType = $this->typeResolver
                ->withPushedScope($bodyScope)
                ->getTypeOfExpression($arrowFunctionNode->returnExpression);
        } catch (\Exception $any) {
            return FunctionType::create(
                $parameterTypes->toTuple(),
                $contract->returnType
            );
        }

        if ($contract->returnType instanceof GenericType) {
            $returnType = $this->knownTypes->contains($contract->returnType)
                ? $this->knownTypes[$contract->returnType]
                : $this->knownTypes[$contract->returnType] = $returnType;
        } else {
            $returnType = $contract->returnType;
        }

        return FunctionType::create(
            $parameterTypes->toTuple(),
            $returnType
        );
    }

    public function inferParameterType(Type $contract, ParameterDeclarationNode $parameterDeclarationNode): Type
    {
        if ($this->knownTypes->contains($contract)) {
            return $this->knownTypes[$contract];
        }

        if ($parameterDeclarationNode->type) {
            $resolvedType = $this->typeResolver
                ->getTypeForTypeReference($parameterDeclarationNode->type);

            if ($contract instanceof GenericType) {
                $this->knownTypes[$contract] = $resolvedType;
            }

            return $resolvedType;
        }

        return $contract;
    }

    public function inferType(Type $type)
    {
        return match ($type::class) {
            GenericType::class => $this->inferGenericType($type),
            ArrayType::class => $this->inferArrayType($type),
            default => $type
        };
    }

    public function inferGenericType(GenericType $genericType): Type
    {
        return $this->knownTypes->contains($genericType)
            ? $this->knownTypes[$genericType]
            : $genericType;
    }

    public function inferArrayType(ArrayType $arrayType): ArrayType
    {
        return ArrayType::of($this->inferType($arrayType->itemType));
    }
}
