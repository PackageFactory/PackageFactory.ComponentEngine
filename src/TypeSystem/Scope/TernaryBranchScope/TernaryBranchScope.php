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

namespace PackageFactory\ComponentEngine\TypeSystem\Scope\TernaryBranchScope;

use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\TypeReferenceNode;
use PackageFactory\ComponentEngine\TypeSystem\Narrower\NarrowedTypes;
use PackageFactory\ComponentEngine\TypeSystem\Narrower\Expression\ExpressionTypeNarrower;
use PackageFactory\ComponentEngine\TypeSystem\Narrower\TypeNarrowerContext;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

final class TernaryBranchScope implements ScopeInterface
{
    private function __construct(
        private readonly NarrowedTypes $narrowedTypes,
        private readonly ScopeInterface $parentScope
    ) {
    }

    public static function forTruthyBranch(ExpressionNode $conditionNode, ScopeInterface $parentScope): self
    {
        return new self(
            (new ExpressionTypeNarrower($parentScope))->narrowTypesOfSymbolsIn($conditionNode, TypeNarrowerContext::TRUTHY),
            $parentScope
        );
    }

    public static function forFalsyBranch(ExpressionNode $conditionNode, ScopeInterface $parentScope): self
    {
        return new self(
            (new ExpressionTypeNarrower($parentScope))->narrowTypesOfSymbolsIn($conditionNode, TypeNarrowerContext::FALSY),
            $parentScope
        );
    }

    public function lookupTypeFor(string $name): ?TypeInterface
    {
        return $this->narrowedTypes->getType($name) ?? $this->parentScope->lookupTypeFor($name);
    }

    public function resolveTypeReference(TypeReferenceNode $typeReferenceNode): TypeInterface
    {
        return $this->parentScope->resolveTypeReference($typeReferenceNode);
    }
}
