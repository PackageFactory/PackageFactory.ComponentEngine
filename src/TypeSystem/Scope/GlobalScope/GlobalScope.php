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

namespace PackageFactory\ComponentEngine\TypeSystem\Scope\GlobalScope;

use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\Domain\VariableName\VariableName;
use PackageFactory\ComponentEngine\Framework\PHP\Singleton\Singleton;
use PackageFactory\ComponentEngine\TypeSystem\AtomicTypeInterface;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\Type\BooleanType\BooleanType;
use PackageFactory\ComponentEngine\TypeSystem\Type\IntegerType\IntegerType;
use PackageFactory\ComponentEngine\TypeSystem\Type\SlotType\SlotType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

final class GlobalScope implements ScopeInterface
{
    use Singleton;

    public function getType(TypeName $typeName): AtomicTypeInterface
    {
        return match ($typeName) {
            StringType::singleton()->getName() => StringType::singleton(),
            IntegerType::singleton()->getName() => IntegerType::singleton(),
            BooleanType::singleton()->getName() => BooleanType::singleton(),
            SlotType::singleton()->getName() => SlotType::singleton(),
            default => throw new \Exception('@TODO: Unknown Type ' . $typeName->value)
        };
    }

    public function getTypeOf(VariableName $variableName): ?TypeInterface
    {
        return null;
    }
}
