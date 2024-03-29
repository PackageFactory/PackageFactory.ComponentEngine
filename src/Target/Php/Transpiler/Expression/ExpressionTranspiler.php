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

namespace PackageFactory\ComponentEngine\Target\Php\Transpiler\Expression;

use PackageFactory\ComponentEngine\Language\AST\Node\Access\AccessNode;
use PackageFactory\ComponentEngine\Language\AST\Node\BinaryOperation\BinaryOperationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\BooleanLiteral\BooleanLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Expression\ExpressionNode;
use PackageFactory\ComponentEngine\Language\AST\Node\IntegerLiteral\IntegerLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Match\MatchNode;
use PackageFactory\ComponentEngine\Language\AST\Node\NullLiteral\NullLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StringLiteral\StringLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\TagNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TernaryOperation\TernaryOperationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\UnaryOperation\UnaryOperationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\ValueReference\ValueReferenceNode;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\Access\AccessTranspiler;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\BinaryOperation\BinaryOperationTranspiler;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\BooleanLiteral\BooleanLiteralTranspiler;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\ValueReference\ValueReferenceTranspiler;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\Match\MatchTranspiler;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\NullLiteral\NullLiteralTranspiler;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\IntegerLiteral\IntegerLiteralTranspiler;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\StringLiteral\StringLiteralTranspiler;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\Tag\TagTranspiler;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\TemplateLiteral\TemplateLiteralTranspiler;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\TernaryOperation\TernaryOperationTranspiler;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\UnaryOperation\UnaryOperationTranspiler;
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
            ValueReferenceNode::class => new ValueReferenceTranspiler(
                scope: $this->scope
            ),
            TernaryOperationNode::class => new TernaryOperationTranspiler(
                scope: $this->scope
            ),
            BinaryOperationNode::class => new BinaryOperationTranspiler(
                scope: $this->scope
            ),
            UnaryOperationNode::class => new UnaryOperationTranspiler(
                scope: $this->scope
            ),
            BooleanLiteralNode::class => new BooleanLiteralTranspiler(),
            MatchNode::class => new MatchTranspiler(
                scope: $this->scope
            ),
            NullLiteralNode::class => new NullLiteralTranspiler(),
            IntegerLiteralNode::class => new IntegerLiteralTranspiler(),
            StringLiteralNode::class => new StringLiteralTranspiler(
                shouldAddQuotes: $this->shouldAddQuotesIfNecessary
            ),
            TagNode::class => new TagTranspiler(
                scope: $this->scope,
                shouldAddQuotes: $this->shouldAddQuotesIfNecessary
            ),
            TemplateLiteralNode::class => new TemplateLiteralTranspiler(
                scope: $this->scope
            )
        };

        return $rootTranspiler->transpile($expressionNode->root);
    }
}
