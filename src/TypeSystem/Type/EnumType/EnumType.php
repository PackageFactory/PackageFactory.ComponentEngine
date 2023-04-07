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

namespace PackageFactory\ComponentEngine\TypeSystem\Type\EnumType;

use PackageFactory\ComponentEngine\Parser\Ast\EnumDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\EnumMemberDeclarationNode;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

final class EnumType implements TypeInterface
{
    private function __construct(
        public readonly string $enumName,
        public readonly array $members,
    ) {
    }

    public static function fromEnumDeclarationNode(EnumDeclarationNode $enumDeclarationNode): self
    {
        return new self(
            enumName: $enumDeclarationNode->enumName,
            members: array_map(
                fn (EnumMemberDeclarationNode $memberDeclarationNode) => $memberDeclarationNode->name,
                $enumDeclarationNode->memberDeclarations->items
            )
        );
    }

    public function is(TypeInterface $other): bool
    {
        // todo more satisfied check with namespace taken into account
        return match ($other::class) {
            EnumType::class => $this->enumName === $other->enumName,
            EnumStaticType::class => $this->enumName === $other->enumName,
            default => false
        };
    }
}
