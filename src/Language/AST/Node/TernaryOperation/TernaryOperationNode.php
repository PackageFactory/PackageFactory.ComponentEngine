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

namespace PackageFactory\ComponentEngine\Language\AST\Node\TernaryOperation;

use PackageFactory\ComponentEngine\Language\AST\Node\Expression\ExpressionNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Node;
use PackageFactory\ComponentEngine\Parser\Source\Range;

final class TernaryOperationNode extends Node
{
    public function __construct(
        public readonly ExpressionNode $condition,
        public readonly ExpressionNode $trueBranch,
        public readonly ExpressionNode $falseBranch
    ) {
        parent::__construct(
            rangeInSource: Range::from(
                $condition->rangeInSource->start,
                $falseBranch->rangeInSource->end
            )
        );
    }
}
