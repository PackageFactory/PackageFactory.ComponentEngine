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
use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\TypeSystem\AtomicTypeInterface;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

final class EnumInstanceType implements AtomicTypeInterface
{
    private function __construct(
        public readonly EnumStaticType $enumStaticType,
        private readonly ?EnumMemberName $name
    ) {
        if ($name !== null && !$enumStaticType->hasMember($name)) {
            throw new \Exception('@TODO cannot access member ' . $name->value . ' of enum ' . $enumStaticType->getName()->value);
        }
    }

    public static function createUnspecifiedEnumInstanceType(EnumStaticType $enumStaticType): self
    {
        return new self(
            enumStaticType: $enumStaticType,
            name: null
        );
    }

    public static function fromStaticEnumTypeAndMemberName(EnumStaticType $enumStaticType, EnumMemberName $enumMemberName): self
    {
        return new self(
            enumStaticType: $enumStaticType,
            name: $enumMemberName
        );
    }

    public function isUnspecified(): bool
    {
        return $this->name === null;
    }

    public function getMemberName(): EnumMemberName
    {
        return $this->name ?? throw new \Exception('@TODO Error cannot access name of unspecified instance');
    }

    public function getName(): TypeName
    {
        return $this->enumStaticType->getName();
    }

    public function is(TypeInterface $other): bool
    {
        if ($other === $this) {
            return true;
        }
        if ($other instanceof EnumInstanceType) {
            return $other->enumStaticType->is($other->enumStaticType)
                && $other->name === $this->name;
        }
        return false;
    }
}
