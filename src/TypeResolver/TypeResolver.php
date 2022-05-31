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

use PackageFactory\ComponentEngine\Parser\Ast\ExportNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\TypeReferenceNode;
use PackageFactory\ComponentEngine\Type\Type;
use PackageFactory\ComponentEngine\TypeResolver\Scope\Scope;

final class TypeResolver
{
    use Resolve\ResolveAccessChain;
    use Resolve\ResolveActualParameters;
    use Resolve\ResolveBinaryOperand;
    use Resolve\ResolveBinaryOperation;
    use Resolve\ResolveComponentDeclaration;
    use Resolve\ResolveComponentInterface;
    use Resolve\ResolveEnumDeclaration;
    use Resolve\ResolveExport;
    use Resolve\ResolveExpression;
    use Resolve\ResolveFunctionCall;
    use Resolve\ResolveInterfaceDeclaration;
    use Resolve\ResolveMatchStatement;
    use Resolve\ResolveNumberLiteral;
    use Resolve\ResolvePropertyDeclaration;
    use Resolve\ResolvePropertyDeclarations;
    use Resolve\ResolveStringLiteral;
    use Resolve\ResolveTag;
    use Resolve\ResolveTemplateLiteral;
    use Resolve\ResolveTernaryOperation;
    use Resolve\ResolveTypeReference;
    use Resolve\ResolveVariable;

    public function __construct(private readonly Scope $scope)
    {
    }

    public function withScope(Scope $scope): self
    {
        return new self(scope: $scope);
    }

    public function withPushedScope(Scope $scope): self
    {
        return new self(scope: $this->scope->push($scope));
    }

    public function getTypedAstForExport(ExportNode $exportNode)
    {
        return $this->resolveExport($exportNode);
    }

    public function getTypeOfExpression(ExpressionNode $expressionNode): Type
    {
        return $this->resolveExpression($expressionNode)->type;
    }

    public function getTypeForTypeReference(TypeReferenceNode $typeReferenceNode): Type
    {
        return $this->scope->lookupType($typeReferenceNode->name) 
            ?? throw new \Exception('@TODO: Unknown Type ' . $typeReferenceNode->name);
    }
}
