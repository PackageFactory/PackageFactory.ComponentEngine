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
use PackageFactory\ComponentEngine\Parser\Ast\StringLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\TemplateLiteralNode;
use PackageFactory\ComponentEngine\Type\ArrayType;
use PackageFactory\ComponentEngine\Type\Type;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\Expression;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\StringLiteral;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\TemplateLiteral;

trait ResolveTemplateLiteral
{
    abstract private function resolveStringLiteral(
        StringLiteralNode $expressionNode
    ): StringLiteral;

    abstract private function resolveExpression(
        ExpressionNode $expressionNode
    ): Expression;

    private function resolveTemplateLiteral(
        TemplateLiteralNode $templateLiteralNode
    ): TemplateLiteral {
        $segments = [];
        foreach ($templateLiteralNode->segments as $segmentNode) {
            $segments[] = match ($segmentNode::class) {
                StringLiteralNode::class => $this->resolveStringLiteral($segmentNode),
                ExpressionNode::class => $this->resolveExpression($segmentNode)
            };
        }

        return new TemplateLiteral(...$segments);
    }
}
