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

namespace PackageFactory\ComponentEngine\Language\AST\Node\BinaryOperation;

enum BinaryOperator: string
{
    case NULLISH_COALESCE = 'NULLISH_COALESCE';

    case AND = 'AND';
    case OR = 'OR';

    case EQUAL = 'EQUAL';
    case NOT_EQUAL = 'NOT_EQUAL';
    case GREATER_THAN = 'GREATER_THAN';
    case GREATER_THAN_OR_EQUAL = 'GREATER_THAN_OR_EQUAL';
    case LESS_THAN = 'LESS_THAN';
    case LESS_THAN_OR_EQUAL = 'LESS_THAN_OR_EQUAL';
}
