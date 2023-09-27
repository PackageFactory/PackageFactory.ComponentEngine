<?php

/**
 * PackageFactory.ComponentEngine - Universal View Components for PHP
 *   Copyright (C) 2023 Contributors of PackageFactory.ComponentEngine
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

namespace PackageFactory\ComponentEngine\Language\AST\Node\TypeReference;

use PackageFactory\ComponentEngine\Language\AST\Node\Node;
use PackageFactory\ComponentEngine\Parser\Source\Range;

final class TypeReferenceNode extends Node
{
    public function __construct(
        public readonly Range $rangeInSource,
        public readonly TypeNameNodes $names,
        public readonly bool $isArray,
        public readonly bool $isOptional
    ) {
        if ($isArray === true && $isOptional === true) {
            throw InvalidTypeReferenceNode::becauseItWasOptionalAndArrayAtTheSameTime(
                affectedTypeNames: $names->toTypeNames(),
                affectedRangeInSource: $rangeInSource
            );
        }

        if ($names->getSize() > 1 && $isArray === true) {
            throw InvalidTypeReferenceNode::becauseItWasUnionAndArrayAtTheSameTime(
                affectedTypeNames: $names->toTypeNames(),
                affectedRangeInSource: $rangeInSource
            );
        }

        if ($names->getSize() > 1 && $isOptional === true) {
            throw InvalidTypeReferenceNode::becauseItWasUnionAndOptionalAtTheSameTime(
                affectedTypeNames: $names->toTypeNames(),
                affectedRangeInSource: $rangeInSource
            );
        }
    }
}
