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

use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

final class EnumInstanceType implements TypeInterface
{
    private function __construct(
        public readonly EnumStaticType $enumStaticType,
        private readonly ?string $memberName
    ) {
        if ($memberName !== null && !$enumStaticType->hasMember($memberName)) {
            throw new \Exception('@TODO cannot access member ' . $memberName . ' of enum ' . $enumStaticType->enumName);
        }
    }

    public static function createUnspecifiedEnumInstanceType(EnumStaticType $enumStaticType): self
    {
        return new self(
            enumStaticType: $enumStaticType,
            memberName: null
        );
    }

    public static function fromStaticEnumTypeAndMemberName(EnumStaticType $enumStaticType, string $enumMemberName): self
    {
        return new self(
            enumStaticType: $enumStaticType,
            memberName: $enumMemberName
        );
    }

    public function isUnspecified(): bool
    {
        return $this->memberName === null;
    }

    public function getMemberName(): string
    {
        return $this->memberName ?? throw new \Exception('@TODO Error cannot access memberName of unspecified instance');
    }

    public function is(TypeInterface $other): bool
    {
        if ($other === $this) {
            return true;
        }
        if ($other instanceof EnumInstanceType) {
            return $other->enumStaticType->is($other->enumStaticType)
                && $other->memberName === $this->memberName;
        }
        return false;
    }
}
