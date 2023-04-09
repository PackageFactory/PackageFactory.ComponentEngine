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

use PackageFactory\ComponentEngine\Module\ModuleId;
use PackageFactory\ComponentEngine\Parser\Ast\EnumDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\NumberLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\StringLiteralNode;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\NumberLiteral\NumberLiteralTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\StringLiteral\StringLiteralTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

trait EnumTrait
{
    public function __construct(
        public readonly ?ModuleId $moduleId,
        public readonly string $enumName,
        private readonly array $membersWithType,
    ) {
    }

    public static function fromModuleIdAndDeclaration(ModuleId $moduleId, EnumDeclarationNode $enumDeclarationNode): self
    {
        $membersWithType = [];

        foreach ($enumDeclarationNode->memberDeclarations->items as $memberDeclarationNode) {
            $membersWithType[$memberDeclarationNode->name] = match ($memberDeclarationNode->value
                ? $memberDeclarationNode->value::class
                : null
            ) {
                NumberLiteralNode::class => (new NumberLiteralTypeResolver())
                    ->resolveTypeOf($memberDeclarationNode->value),
                StringLiteralNode::class => (new StringLiteralTypeResolver())
                    ->resolveTypeOf($memberDeclarationNode->value),
                null => null
            };
        }

        return new self(
            moduleId: $moduleId,
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
        return match ($other::class) {
            EnumInstanceType::class, EnumStaticType::class =>
                $this->moduleId === $other->moduleId
                && $this->enumName === $other->enumName,
            default => false
        };
    }
}
