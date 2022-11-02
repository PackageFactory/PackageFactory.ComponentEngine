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

namespace PackageFactory\ComponentEngine\Parser\Ast;

use PackageFactory\ComponentEngine\Parser\Ast\ComponentDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\EnumDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\InterfaceDeclarationNode;

final class ExportNodes implements \JsonSerializable
{
    /**
     * @var array<string,ExportNode>
     */
    public readonly array $items;

    private function __construct(
        ExportNode ...$items
    ) {
        $this->items = $items;
    }

    public static function empty(): self
    {
        return new self();
    }

    public function withAddedExport(ExportNode $export): self
    {
        $name = match ($export->declaration::class) {
            ComponentDeclarationNode::class => $export->declaration->componentName,
            StructDeclarationNode::class => $export->declaration->structName,
            EnumDeclarationNode::class => $export->declaration->enumName
        };

        if (array_key_exists($name, $this->items)) {
            throw new \Exception('@TODO: Duplicate Export ' . $name);
        }

        return new self(...$this->items, ...[$name => $export]);
    }

    public function get(string $name): ?ExportNode
    {
        return $this->items[$name] ?? null;
    }

    public function jsonSerialize(): mixed
    {
        return array_values($this->items);
    }
}
