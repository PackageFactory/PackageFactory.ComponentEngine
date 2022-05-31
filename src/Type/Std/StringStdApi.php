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

namespace PackageFactory\ComponentEngine\Type\Std;

use PackageFactory\ComponentEngine\Type\FunctionType;
use PackageFactory\ComponentEngine\Type\MethodType;
use PackageFactory\ComponentEngine\Type\Primitive\BooleanType;
use PackageFactory\ComponentEngine\Type\Primitive\NumberType;
use PackageFactory\ComponentEngine\Type\Primitive\StringType;
use PackageFactory\ComponentEngine\Type\Record\RecordEntry;
use PackageFactory\ComponentEngine\Type\Record\RecordType;
use PackageFactory\ComponentEngine\Type\Tuple;

final class StringStdApi
{
    private static null|RecordType $stdApi = null;

    private static function make(): RecordType
    {
        return RecordType::of(
            RecordEntry::of('toUpperCase', MethodType::create(
                StringType::create(),
                FunctionType::create(
                    Tuple::of(),
                    StringType::create()
                )
            )),
            RecordEntry::of('indexOf', MethodType::create(
                StringType::create(),
                FunctionType::create(
                    Tuple::of(StringType::create()),
                    NumberType::create()
                )
            )),
            RecordEntry::of('substr', MethodType::create(
                StringType::create(),
                FunctionType::create(
                    Tuple::of(NumberType::create(), NumberType::create()),
                    StringType::create()
                )
            )),
            RecordEntry::of('startsWith', MethodType::create(
                StringType::create(),
                FunctionType::create(
                    Tuple::of(StringType::create()),
                    BooleanType::create()
                )
            ))
        );
    }

    public static function get(): RecordType
    {
        return self::$stdApi ?? (self::$stdApi = self::make());
    }
}
