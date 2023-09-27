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

namespace PackageFactory\ComponentEngine\TypeSystem\Type\ComponentType;

use PackageFactory\ComponentEngine\Domain\ComponentName\ComponentName;
use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\Language\AST\Node\ComponentDeclaration\ComponentDeclarationNode;
use PackageFactory\ComponentEngine\TypeSystem\AtomicTypeInterface;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

final class ComponentType implements AtomicTypeInterface
{
    private function __construct(private readonly ComponentName $name)
    {
    }

    public static function fromComponentDeclarationNode(ComponentDeclarationNode $componentDeclarationNode): self
    {
        return new self($componentDeclarationNode->name->value);
    }

    public function getName(): TypeName
    {
        return $this->name->toTypeName();
    }

    public function is(TypeInterface $other): bool
    {
        return $other === $this;
    }
}
