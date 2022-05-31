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
use PackageFactory\ComponentEngine\Parser\Ast\MatchArmNode;
use PackageFactory\ComponentEngine\Parser\Ast\MatchArmNodes;
use PackageFactory\ComponentEngine\Parser\Ast\MatchNode;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\Expression;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\Expressions;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\MatchArm;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\MatchArms;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\MatchStatement;

trait ResolveMatchStatement
{
    abstract private function resolveExpression(
        ExpressionNode $expressionNode
    ): Expression;

    private function resolveMatchStatement(
        MatchNode $matchNode
    ): MatchStatement {
        $arms = $this->resolveMatchArms($matchNode->arms);
        [$firstArm] = $arms->items;

        $type = $firstArm->result->type;
        foreach ($arms->items as $arm) {
            if ($arm !== $firstArm) {
                $type = $type->expand($arm->result->type);
            }
        }

        return new MatchStatement(
            subject: $this->resolveExpression($matchNode->subject),
            arms: $this->resolveMatchArms($matchNode->arms),
            type: $type
        );
    }

    private function resolveMatchArms(MatchArmNodes $matchArmNodes): MatchArms
    {
        $items = [];
        foreach ($matchArmNodes->items as $matchArmNode) {
            $items[] = $this->resolveMatchArm($matchArmNode);
        }

        return new MatchArms(...$items);
    }

    private function resolveMatchArm(MatchArmNode $matchArmNode): MatchArm
    {
        $conditions = [];
        if ($matchArmNode->left) {
            foreach ($matchArmNode->left->items as $conditionNode) {
                $conditions[] = $this->resolveExpression($conditionNode);
            }
        }

        return new MatchArm(
            conditions: count($conditions)
                ? new Expressions(...$conditions)
                : null,
            result: $this->resolveExpression($matchArmNode->right)
        );
    }
}
