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

use PackageFactory\ComponentEngine\Language\AST\ASTException;

final class InvalidTypeNameNodes extends ASTException
{
    public static function becauseTheyWereEmpty(): self
    {
        return new self(
            code: 1690549442,
            message: 'A type reference must refer to at least one type name.'
        );
    }

    public static function becauseTheyContainDuplicates(
        TypeNameNode $duplicateTypeNameNode
    ): self {
        return new self(
            code: 1690551330,
            message: 'A type reference must not contain duplicates.',
            attributesOfAffectedNode: $duplicateTypeNameNode->attributes
        );
    }
}
