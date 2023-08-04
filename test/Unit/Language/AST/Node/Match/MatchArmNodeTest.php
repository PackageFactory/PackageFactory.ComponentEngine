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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\AST\Node\Match;

use PackageFactory\ComponentEngine\Domain\VariableName\VariableName;
use PackageFactory\ComponentEngine\Language\AST\Node\Expression\ExpressionNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Expression\ExpressionNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Match\MatchArmNode;
use PackageFactory\ComponentEngine\Language\AST\Node\ValueReference\ValueReferenceNode;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PHPUnit\Framework\TestCase;

final class MatchArmNodeTest extends TestCase
{
    /**
     * @test
     */
    public function matchArmWithEmptyLeftIsDefault(): void
    {
        $zeroRange = Range::from(new Position(0, 0), new Position(0, 0));
        $matchArmNode = new MatchArmNode(
            rangeInSource: $zeroRange,
            left: null,
            right: new ExpressionNode(
                rangeInSource: $zeroRange,
                root: new ValueReferenceNode(
                    rangeInSource: $zeroRange,
                    name: VariableName::from('foo')
                )
            )
        );

        $this->assertTrue($matchArmNode->isDefault());
    }

    /**
     * @test
     */
    public function matchArmWithNonEmptyLeftIsNotDefault(): void
    {
        $zeroRange = Range::from(new Position(0, 0), new Position(0, 0));
        $matchArmNode = new MatchArmNode(
            rangeInSource: $zeroRange,
            left: new ExpressionNodes(
                new ExpressionNode(
                    rangeInSource: $zeroRange,
                    root: new ValueReferenceNode(
                        rangeInSource: $zeroRange,
                        name: VariableName::from('foo')
                    )
                ),
                new ExpressionNode(
                    rangeInSource: $zeroRange,
                    root: new ValueReferenceNode(
                        rangeInSource: $zeroRange,
                        name: VariableName::from('bar')
                    )
                )
            ),
            right: new ExpressionNode(
                rangeInSource: $zeroRange,
                root: new ValueReferenceNode(
                    rangeInSource: $zeroRange,
                    name: VariableName::from('baz')
                )
            )
        );

        $this->assertFalse($matchArmNode->isDefault());
    }
}
