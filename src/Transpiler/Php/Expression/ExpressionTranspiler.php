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

namespace PackageFactory\ComponentEngine\Transpiler\Php\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\AccessNode;
use PackageFactory\ComponentEngine\Parser\Ast\BinaryOperationNode;
use PackageFactory\ComponentEngine\Parser\Ast\BooleanLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\IdentifierNode;
use PackageFactory\ComponentEngine\Parser\Ast\MatchNode;
use PackageFactory\ComponentEngine\Parser\Ast\NumberLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\StringLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\TagNode;
use PackageFactory\ComponentEngine\Parser\Ast\TernaryOperationNode;
use PackageFactory\ComponentEngine\Transpiler\Php\Access\AccessTranspiler;
use PackageFactory\ComponentEngine\Transpiler\Php\BinaryOperation\BinaryOperationTranspiler;
use PackageFactory\ComponentEngine\Transpiler\Php\BooleanLiteral\BooleanLiteralTranspiler;
use PackageFactory\ComponentEngine\Transpiler\Php\Identifier\IdentifierTranspiler;
use PackageFactory\ComponentEngine\Transpiler\Php\Match\MatchTranspiler;
use PackageFactory\ComponentEngine\Transpiler\Php\NumberLiteral\NumberLiteralTranspiler;
use PackageFactory\ComponentEngine\Transpiler\Php\StringLiteral\StringLiteralTranspiler;
use PackageFactory\ComponentEngine\Transpiler\Php\Tag\TagTranspiler;
use PackageFactory\ComponentEngine\Transpiler\Php\TernaryOperation\TernaryOperationTranspiler;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;

final class ExpressionTranspiler
{
    public function __construct(
        private readonly ScopeInterface $scope, 
        private readonly bool $shouldAddQuotesIfNecessary = false
    ) {
    }

    public function transpile(ExpressionNode $expressionNode): string
    {
        $rootTranspiler = match ($expressionNode->root::class) {
            AccessNode::class => new AccessTranspiler(
                scope: $this->scope
            ),
            IdentifierNode::class => new IdentifierTranspiler(
                scope: $this->scope
            ),
            TernaryOperationNode::class => new TernaryOperationTranspiler(
                scope: $this->scope
            ),
            BinaryOperationNode::class => new BinaryOperationTranspiler(
                scope: $this->scope
            ),
            BooleanLiteralNode::class => new BooleanLiteralTranspiler(),
            MatchNode::class => new MatchTranspiler(
                scope: $this->scope
            ),
            NumberLiteralNode::class => new NumberLiteralTranspiler(),
            StringLiteralNode::class => new StringLiteralTranspiler(
                shouldAddQuotes: $this->shouldAddQuotesIfNecessary
            ),
            TagNode::class => new TagTranspiler(
                scope: $this->scope,
                shouldAddQuotes: $this->shouldAddQuotesIfNecessary
            ),
            default => throw new \Exception('@TODO: Transpile ' . $expressionNode->root::class)
        };

        return $rootTranspiler->transpile($expressionNode->root);
    }
}
