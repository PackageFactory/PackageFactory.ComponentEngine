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

use PackageFactory\ComponentEngine\Domain\EnumMemberName\EnumMemberName;
use PackageFactory\ComponentEngine\Domain\EnumName\EnumName;
use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumDeclarationNode;
use PackageFactory\ComponentEngine\Module\ModuleId;
use PackageFactory\ComponentEngine\TypeSystem\AtomicTypeInterface;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

final class EnumStaticType implements AtomicTypeInterface
{
    /**
     * @param EnumName $name
     * @param ModuleId $moduleId
     * @param array<string,bool> $memberNameHashMap
     */
    public function __construct(
        private readonly EnumName $name,
        private readonly ModuleId $moduleId,
        private readonly array $memberNameHashMap,
    ) {
    }

    public static function fromModuleIdAndDeclaration(ModuleId $moduleId, EnumDeclarationNode $enumDeclarationNode): self
    {
        $memberNameHashMap = [];
        foreach ($enumDeclarationNode->members->items as $memberDeclarationNode) {
            $memberNameHashMap[$memberDeclarationNode->name->value->value] = true;
        }

        return new self(
            name: $enumDeclarationNode->name->value,
            moduleId: $moduleId,
            memberNameHashMap: $memberNameHashMap
        );
    }

    /**
     * @return string[]
     */
    public function getMemberNames(): array
    {
        return array_keys($this->memberNameHashMap);
    }

    public function hasMember(EnumMemberName $memberName): bool
    {
        return array_key_exists($memberName->value, $this->memberNameHashMap);
    }

    public function getMemberType(EnumMemberName $memberName): EnumInstanceType
    {
        return EnumInstanceType::fromStaticEnumTypeAndMemberName(
            $this,
            $memberName
        );
    }

    public function getName(): TypeName
    {
        return $this->name->toTypeName();
    }

    public function is(TypeInterface $other): bool
    {
        if ($other === $this) {
            return true;
        }
        if ($other instanceof EnumStaticType) {
            return $this->moduleId === $other->moduleId
                && $this->name->value === $other->name->value;
        }
        return false;
    }

    public function toEnumInstanceType(): EnumInstanceType
    {
        return EnumInstanceType::createUnspecifiedEnumInstanceType($this);
    }
}
