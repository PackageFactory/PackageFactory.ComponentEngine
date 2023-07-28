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

use PackageFactory\ComponentEngine\Domain\TypeName\TypeNames;
use PackageFactory\ComponentEngine\Language\AST\ASTException;
use PackageFactory\ComponentEngine\Language\AST\NodeAttributes\NodeAttributes;

final class InvalidTypeReferenceNode extends ASTException
{
    public static function becauseItWasOptionalAndArrayAtTheSameTime(
        TypeNames $affectedTypeNames,
        NodeAttributes $attributesOfAffectedNode
    ): self {
        return new self(
            code: 1690538480,
            message: sprintf(
                'The type reference to "%s" must not be optional and array at the same time.',
                $affectedTypeNames->toDebugString()
            ),
            attributesOfAffectedNode: $attributesOfAffectedNode
        );
    }

    public static function becauseItWasUnionAndArrayAtTheSameTime(
        TypeNames $affectedTypeNames,
        NodeAttributes $attributesOfAffectedNode
    ): self {
        return new self(
            code: 1690552344,
            message: sprintf(
                'The type reference to "%s" must not be union and array at the same time.',
                $affectedTypeNames->toDebugString()
            ),
            attributesOfAffectedNode: $attributesOfAffectedNode
        );
    }

    public static function becauseItWasUnionAndOptionalAtTheSameTime(
        TypeNames $affectedTypeNames,
        NodeAttributes $attributesOfAffectedNode
    ): self {
        return new self(
            code: 1690552586,
            message: sprintf(
                'The type reference to "%s" must not be union and optional at the same time.',
                $affectedTypeNames->toDebugString()
            ),
            attributesOfAffectedNode: $attributesOfAffectedNode
        );
    }
}
