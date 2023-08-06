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

namespace PackageFactory\ComponentEngine\Target\Php\Transpiler\TagContent;

use PackageFactory\ComponentEngine\Language\AST\Node\Expression\ExpressionNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\TagNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Text\TextNode;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\Expression\ExpressionTranspiler;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\Tag\TagTranspiler;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\Text\TextTranspiler;
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

    public function transpile(TextNode|ExpressionNode|TagNode $tagContentNode): string
    {
        return match ($tagContentNode::class) {
            TextNode::class => (new TextTranspiler())->transpile($tagContentNode),
            ExpressionNode::class => $this->transpileExpression($tagContentNode),
            TagNode::class => (new TagTranspiler(
                scope: $this->scope
            ))->transpile($tagContentNode)
        };
    }
}
