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

use PackageFactory\ComponentEngine\Parser\Ast;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst;

trait ResolveExpression
{
    abstract private function resolveAccessChain(
        Ast\AccessNode $accessNode
    ): TypedAst\AccessChain;

    abstract private function resolveBinaryOperation(
        Ast\BinaryOperationNode $binaryOperationNode
    ): TypedAst\BinaryOperation;

    abstract private function resolveFunctionCall(
        Ast\FunctionCallNode $functionCallNode
    ): TypedAst\FunctionCall;

    abstract private function resolveMatchStatement(
        Ast\MatchNode $matchNode
    ): TypedAst\MatchStatement;

    abstract private function resolveNumberLiteral(
        Ast\NumberLiteralNode $numberLiteralNode
    ): TypedAst\NumberLiteral;

    abstract private function resolveStringLiteral(
        Ast\StringLiteralNode $accessNode
    ): TypedAst\StringLiteral;

    abstract private function resolveTag(
        Ast\TagNode $tagNode
    ): TypedAst\Tag;

    abstract private function resolveTemplateLiteral(
        Ast\TemplateLiteralNode $templateLiteralNode
    ): TypedAst\TemplateLiteral;

    abstract private function resolveTernaryOperation(
        Ast\TernaryOperationNode $ternaryOperationNode
    ): TypedAst\TernaryOperation;

    abstract private function resolveVariable(
        Ast\IdentifierNode $identifierNode
    ): TypedAst\Variable;

    private function resolveExpression(
        Ast\ExpressionNode $expressionNode
    ): TypedAst\Expression {
        $root = match ($expressionNode->root::class) {
            Ast\AccessNode::class => $this->resolveAccessChain(
                $expressionNode->root
            ),
            Ast\BinaryOperationNode::class => $this->resolveBinaryOperation(
                $expressionNode->root
            ),
            Ast\IdentifierNode::class => $this->resolveVariable(
                $expressionNode->root
            ),
            Ast\FunctionCallNode::class => $this->resolveFunctionCall(
                $expressionNode->root
            ),
            Ast\NumberLiteralNode::class => $this->resolveNumberLiteral(
                $expressionNode->root
            ),
            Ast\MatchNode::class => $this->resolveMatchStatement(
                $expressionNode->root
            ),
            Ast\StringLiteralNode::class => $this->resolveStringLiteral(
                $expressionNode->root
            ),
            Ast\TagNode::class => $this->resolveTag(
                $expressionNode->root
            ),
            Ast\TemplateLiteralNode::class => $this->resolveTemplateLiteral(
                $expressionNode->root
            ),
            Ast\TernaryOperationNode::class => $this->resolveTernaryOperation(
                $expressionNode->root
            ),
            default => throw new \Exception('@TODO: Unimplemented resolution: ' . $expressionNode->root::class)
        };

        return new TypedAst\Expression(
            root: $root,
            type: $root->type
        );
    }
}
