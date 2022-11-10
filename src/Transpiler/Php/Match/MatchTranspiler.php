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

namespace PackageFactory\ComponentEngine\Transpiler\Php\Match;

use PackageFactory\ComponentEngine\Parser\Ast\MatchNode;
use PackageFactory\ComponentEngine\Transpiler\Php\Expression\ExpressionTranspiler;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;

final class MatchTranspiler
{
    public function __construct(private readonly ScopeInterface $scope)
    {
    }

    public function transpile(MatchNode $matchNode): string
    {
        $expressionTranspiler = new ExpressionTranspiler(
            scope: $this->scope,
            shouldAddQuotesIfNecessary: true
        );

        $transpiledSubject = $expressionTranspiler->transpile($matchNode->subject);
        $transpiledArms = [];

        foreach ($matchNode->arms->items as $matchArmNode) {
            $left = [];
            if ($matchArmNode->left === null) {
                $left = ['default'];
            } else {
                foreach ($matchArmNode->left->items as $leftNode) {
                    $left[] = $expressionTranspiler->transpile($leftNode);
                }
            }
            $transpiledArms[] = sprintf(
                '%s => %s',
                join(', ', $left),
                $expressionTranspiler->transpile($matchArmNode->right)
            );
        }

        return sprintf(
            'match (%s) { %s }',
            $transpiledSubject,
            join(', ', $transpiledArms)
        );
    }
}
