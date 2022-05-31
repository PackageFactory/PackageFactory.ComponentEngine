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

use PackageFactory\ComponentEngine\Parser\Ast\AttributeNode;
use PackageFactory\ComponentEngine\Parser\Ast\AttributeNodes;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\StringLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\TagContentNodes;
use PackageFactory\ComponentEngine\Parser\Ast\TagNode;
use PackageFactory\ComponentEngine\Parser\Ast\TextNode;
use PackageFactory\ComponentEngine\Type\Primitive\ElementType;
use PackageFactory\ComponentEngine\TypeResolver\Scope\Scope;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\Attribute;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\Attributes;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\Expression;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\Identifier;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\StringLiteral;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\Tag;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\TagContents;
use PackageFactory\ComponentEngine\TypeResolver\TypedAst\Text;

trait ResolveTag
{
    private readonly Scope $scope;

    abstract private function resolveStringLiteral(
        StringLiteralNode $expressionNode
    ): StringLiteral;

    abstract private function resolveExpression(
        ExpressionNode $expressionNode
    ): Expression;

    private function resolveTag(TagNode $tagNode): Tag
    {
        $tagNameAsString = $tagNode->tagName;

        $type = ctype_upper($tagNameAsString[0])
            ? $this->scope->lookupType($tagNameAsString)
            : ElementType::create();


        return new Tag(
            tagName: new Identifier($tagNameAsString),
            attributes: $this->resolveAttributes($tagNode->attributes),
            contents: $this->resolveTagContents($tagNode->children),
            type: $type
        );
    }

    private function resolveAttributes(AttributeNodes $attributeNodes): Attributes
    {
        /** @var Attribute[] $items */
        $items = [];
        foreach ($attributeNodes->items as $attributeNode) {
            $items[] = $this->resolveAttribute($attributeNode);
        }

        return new Attributes(...$items);
    }

    private function resolveAttribute(AttributeNode $attributeNode): Attribute
    {
        $value = match ($attributeNode->value::class) {
            ExpressionNode::class => $this->resolveExpression($attributeNode->value),
            StringLiteralNode::class => $this->resolveStringLiteral($attributeNode->value)
        };

        return new Attribute(
            name: new Identifier($attributeNode->name),
            value: $value,
            type: $value->type
        );
    }

    private function resolveTagContents(TagContentNodes $tagContentNodes): TagContents
    {
        /** @var (Text|Expression|Tag)[] $items */
        $items = [];
        foreach ($tagContentNodes->items as $tagContentNode) {
            $items[] = match ($tagContentNode->root::class) {
                TextNode::class => $this->resolveText($tagContentNode->root),
                ExpressionNode::class => $this->resolveExpression($tagContentNode->root),
                TagNode::class => $this->resolveTag($tagContentNode->root)
            };
        }

        return new TagContents(...$items);
    }

    private function resolveText(TextNode $textNode): Text
    {
        return new Text($textNode->value);
    }
}
