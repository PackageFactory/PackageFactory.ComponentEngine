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
use PackageFactory\ComponentEngine\Language\AST\Node\Match\InvalidMatchArmNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Match\MatchArmNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Match\MatchArmNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\ValueReference\ValueReferenceNode;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PHPUnit\Framework\TestCase;

final class MatchArmNodesTest extends TestCase
{
    /**
     * @param string[] $left
     * @param string $right
     * @return MatchArmNode
     */
    protected function createMatchArmNode(array $left, string $right): MatchArmNode
    {
        $zeroRange = Range::from(new Position(0, 0), new Position(0, 0));

        return new MatchArmNode(
            rangeInSource: $zeroRange,
            left: new ExpressionNodes(
                ...array_map(
                    static fn(string $name) => new ExpressionNode(
                        rangeInSource: $zeroRange,
                        root: new ValueReferenceNode(
                            rangeInSource: $zeroRange,
                            name: VariableName::from($name)
                        )
                    ),
                    $left
                )
            ),
            right: new ExpressionNode(
                rangeInSource: $zeroRange,
                root: new ValueReferenceNode(
                    rangeInSource: $zeroRange,
                    name: VariableName::from($right)
                )
            )
        );

    }
    protected function createDefaultMatchArmNode(string $right): MatchArmNode
    {
        $zeroRange = Range::from(new Position(0, 0), new Position(0, 0));

        return new MatchArmNode(
            rangeInSource: $zeroRange,
            left: null,
            right: new ExpressionNode(
                rangeInSource: $zeroRange,
                root: new ValueReferenceNode(
                    rangeInSource: $zeroRange,
                    name: VariableName::from($right)
                )
            )
        );
    }

    /**
     * @test
     */
    public function mustNotBeEmpty(): void
    {
        $this->expectExceptionObject(
            InvalidMatchArmNodes::becauseTheyWereEmpty()
        );

        new MatchArmNodes();
    }

    /**
     * @test
     */
    public function mustNotContainMultipleDefaultArms(): void
    {
        $secondDefaultMatchArmNode = $this->createDefaultMatchArmNode('bar');

        $this->expectExceptionObject(
            InvalidMatchArmNodes::becauseTheyContainMoreThanOneDefaultMatchArmNode(
                secondDefaultMatchArmNode: $secondDefaultMatchArmNode
            )
        );

        new MatchArmNodes(
            $this->createMatchArmNode(['a', 'b'], 'c'),
            $this->createDefaultMatchArmNode('foo'),
            $secondDefaultMatchArmNode
        );
    }
}
