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

use PackageFactory\ComponentEngine\Type\ArrayType;
use PackageFactory\ComponentEngine\Type\FunctionType;
use PackageFactory\ComponentEngine\Type\Generic\GenericType;
use PackageFactory\ComponentEngine\Type\MethodType;
use PackageFactory\ComponentEngine\Type\Record\RecordEntry;
use PackageFactory\ComponentEngine\Type\Record\RecordType;
use PackageFactory\ComponentEngine\Type\Tuple;

final class ArrayStdApi
{
    public static function for(ArrayType $instanceType): RecordType
    {
        return RecordType::of(
            RecordEntry::of(
                'map',
                MethodType::create(
                    $instanceType,
                    FunctionType::create(
                        Tuple::of(
                            FunctionType::create(
                                Tuple::of($instanceType->itemType),
                                $T = GenericType::of('T')
                            )
                        ),
                        ArrayType::of($T)
                    )
                )
            ),
            RecordEntry::of(
                'reduce',
                MethodType::create(
                    $instanceType,
                    FunctionType::create(
                        Tuple::of(
                            FunctionType::create(
                                Tuple::of(
                                    $T = GenericType::of('T'),
                                    $instanceType->itemType
                                ),
                                $T
                            ),
                            $T
                        ),
                        $T
                    )
                )
            )
        );
    }
}
