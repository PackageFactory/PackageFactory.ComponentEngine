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
use PackageFactory\ComponentEngine\Parser\Ast\NumberLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\StringLiteralNode;
use PackageFactory\ComponentEngine\TypeSystem\Type\NumberType\NumberType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

trait EnumTrait
{
    public function __construct(
        public readonly string $enumName,
        private readonly array $membersWithType,
    ) {
    }

    public static function fromEnumDeclarationNode(EnumDeclarationNode $enumDeclarationNode): self
    {
        $membersWithType = [];

        foreach ($enumDeclarationNode->memberDeclarations->items as $memberDeclarationNode) {
            $membersWithType[$memberDeclarationNode->name] = match ($memberDeclarationNode->value
                ? $memberDeclarationNode->value::class
                : null
            ) {
                StringLiteralNode::class => StringType::get(),
                NumberLiteralNode::class => NumberType::get(),
                null => null
            };
        }

        return new self(
            enumName: $enumDeclarationNode->enumName,
            membersWithType: $membersWithType
        );
    }
    
    public function getMemberNames(): array
    {
        return array_keys($this->membersWithType);
    }

    public function getMemberType(string $memberName): EnumMemberType
    {
        if (!array_key_exists($memberName, $this->membersWithType)) {
            throw new \Exception('@TODO cannot access member ' . $memberName . ' of enum ' . $this->enumName);
        }
        return new EnumMemberType(
            $this,
            $memberName,
            $this->membersWithType[$memberName]
        );
    }

    public function is(TypeInterface $other): bool
    {
        // todo more satisfied check with namespace taken into account
        return match ($other::class) {
            EnumType::class, EnumStaticType::class => $this->enumName === $other->enumName,
            default => false
        };
    }
}
