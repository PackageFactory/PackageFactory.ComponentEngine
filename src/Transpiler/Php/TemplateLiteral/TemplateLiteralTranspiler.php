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

namespace PackageFactory\ComponentEngine\Transpiler\Php\TemplateLiteral;

use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\StringLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\TemplateLiteralNode;
use PackageFactory\ComponentEngine\Transpiler\Php\Expression\ExpressionTranspiler;
use PackageFactory\ComponentEngine\Transpiler\Php\StringLiteral\StringLiteralTranspiler;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;

final class TemplateLiteralTranspiler
{
    public function __construct(private readonly ScopeInterface $scope) 
    {
    }

    public function transpile(TemplateLiteralNode $templateLiteralNode): string
    {
        $stringLiteralTranspiler = new StringLiteralTranspiler(
            shouldAddQuotes: true
        );
        $expressionTranspiler = new ExpressionTranspiler(
            scope: $this->scope,
            shouldAddQuotesIfNecessary: true
        );
        $segments = [];

        foreach ($templateLiteralNode->segments as $segmentNode) {
            $segments[] = match ($segmentNode::class) {
                StringLiteralNode::class => $stringLiteralTranspiler->transpile($segmentNode),
                ExpressionNode::class => $expressionTranspiler->transpile($segmentNode)
            };
        }

        return join(' . ', $segments);
    }
}
