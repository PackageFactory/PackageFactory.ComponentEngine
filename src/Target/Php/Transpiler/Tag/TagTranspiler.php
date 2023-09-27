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

namespace PackageFactory\ComponentEngine\Target\Php\Transpiler\Tag;

use PackageFactory\ComponentEngine\Language\AST\Node\Tag\TagNode;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\Attribute\AttributeTranspiler;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\TagContent\TagContentTranspiler;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;

final class TagTranspiler
{
    public function __construct(
        private readonly ScopeInterface $scope,
        private readonly bool $shouldAddQuotes = false
    ) {
    }

    public function transpile(TagNode $tagNode): string
    {
        $result = sprintf('<%s', $tagNode->name->value->value);

        $attributeTranspiler = null;
        foreach ($tagNode->attributes->items as $attribute) {
            $attributeTranspiler ??= new AttributeTranspiler(
                scope: $this->scope
            );
            $result .= ' ' . $attributeTranspiler->transpile($attribute);
        }

        if ($tagNode->isSelfClosing) {
            $result .= ' />';
        } else {
            $tagContentTranspiler = new TagContentTranspiler(
                scope: $this->scope
            );

            $result .= '>';

            foreach ($tagNode->children->items as $child) {
                $result .= $tagContentTranspiler->transpile($child);
            }

            $result .= sprintf('</%s>', $tagNode->name->value->value);
        }

        return $this->shouldAddQuotes
            ? sprintf('\'%s\'', $result)
            : $result;
    }
}
