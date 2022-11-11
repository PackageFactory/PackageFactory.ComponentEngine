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

namespace PackageFactory\ComponentEngine\Transpiler\Php\TagContent;

use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\TagContentNode;
use PackageFactory\ComponentEngine\Parser\Ast\TagNode;
use PackageFactory\ComponentEngine\Parser\Ast\TextNode;
use PackageFactory\ComponentEngine\Transpiler\Php\Expression\ExpressionTranspiler;
use PackageFactory\ComponentEngine\Transpiler\Php\Tag\TagTranspiler;
use PackageFactory\ComponentEngine\Transpiler\Php\Text\TextTranspiler;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\Expression\ExpressionTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\Type\ComponentType\ComponentType;

final class TagContentTranspiler
{
    public function __construct(private readonly ScopeInterface $scope)
    {
    }

    private function transpileExpression(ExpressionNode $expressionNode): string
    {
        $expressionTypeResolver = new ExpressionTypeResolver(
            scope: $this->scope
        );
        $expressionTranspiler = new ExpressionTranspiler(
            scope: $this->scope
        );

        $typeOfResult = $expressionTypeResolver->resolveTypeOf($expressionNode);

        $result = $expressionTranspiler->transpile($expressionNode);
        $result = match ($typeOfResult::class) {
            ComponentType::class => $result . '->render()',
            default => $result
        };
        $result = sprintf(
            '\' . %s . \'',
            $result
        );

        return $result;
    }

    public function transpile(TagContentNode $tagContentNode): string
    {
        return match ($tagContentNode->root::class) {
            TextNode::class => (new TextTranspiler())->transpile($tagContentNode->root),
            ExpressionNode::class => $this->transpileExpression($tagContentNode->root),
            TagNode::class => (new TagTranspiler(
                scope: $this->scope
            ))->transpile($tagContentNode->root)
        };
    }
}
