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

namespace PackageFactory\ComponentEngine\TypeSystem\Type\StringType;

use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\Framework\PHP\Singleton\Singleton;
use PackageFactory\ComponentEngine\TypeSystem\AtomicTypeInterface;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

final class StringType implements AtomicTypeInterface
{
    use Singleton;

    public function getName(): TypeName
    {
        return TypeName::from('string');
    }

    public function is(TypeInterface $other): bool
    {
        return $other === self::$instance;
    }
}
