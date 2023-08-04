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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Parser\Match;

use PackageFactory\ComponentEngine\Domain\VariableName\VariableName;
use PackageFactory\ComponentEngine\Language\AST\Node\Expression\ExpressionNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Expression\ExpressionNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Match\InvalidMatchArmNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Match\MatchArmNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Match\MatchArmNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\Match\MatchNode;
use PackageFactory\ComponentEngine\Language\AST\Node\ValueReference\ValueReferenceNode;
use PackageFactory\ComponentEngine\Language\Parser\Match\MatchCouldNotBeParsed;
use PackageFactory\ComponentEngine\Language\Parser\Match\MatchParser;
use PackageFactory\ComponentEngine\Language\Parser\ParserException;
use PackageFactory\ComponentEngine\Test\Unit\Language\Parser\ParserTestCase;

final class MatchParserTest extends ParserTestCase
{
    /**
     * @test
     */
    public function parsesMatchWithOneArm(): void
    {
        $matchParser = new MatchParser();
        $tokens = $this->createTokenIterator(
            'match (a) { b -> c }'
        );

        $expectedMatchNode = new MatchNode(
            rangeInSource: $this->range([0, 0], [0, 19]),
            subject: new ExpressionNode(
                rangeInSource: $this->range([0, 6], [0, 8]),
                root: new ValueReferenceNode(
                    rangeInSource: $this->range([0, 7], [0, 7]),
                    name: VariableName::from('a')
                )
            ),
            arms: new MatchArmNodes(
                new MatchArmNode(
                    rangeInSource: $this->range([0, 12], [0, 17]),
                    left: new ExpressionNodes(
                        new ExpressionNode(
                            rangeInSource: $this->range([0, 12], [0, 12]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 12], [0, 12]),
                                name: VariableName::from('b')
                            )
                        )
                    ),
                    right: new ExpressionNode(
                        rangeInSource: $this->range([0, 17], [0, 17]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 17], [0, 17]),
                                name: VariableName::from('c')
                            )
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedMatchNode,
            $matchParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesMatchWithMultipleArms(): void
    {
        $matchParser = new MatchParser();
        $tokens = $this->createTokenIterator(
            'match (a) { b -> c d -> e f -> g }'
        );

        $expectedMatchNode = new MatchNode(
            rangeInSource: $this->range([0, 0], [0, 33]),
            subject: new ExpressionNode(
                rangeInSource: $this->range([0, 6], [0, 8]),
                root: new ValueReferenceNode(
                    rangeInSource: $this->range([0, 7], [0, 7]),
                    name: VariableName::from('a')
                )
            ),
            arms: new MatchArmNodes(
                new MatchArmNode(
                    rangeInSource: $this->range([0, 12], [0, 17]),
                    left: new ExpressionNodes(
                        new ExpressionNode(
                            rangeInSource: $this->range([0, 12], [0, 12]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 12], [0, 12]),
                                name: VariableName::from('b')
                            )
                        )
                    ),
                    right: new ExpressionNode(
                        rangeInSource: $this->range([0, 17], [0, 17]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 17], [0, 17]),
                                name: VariableName::from('c')
                            )
                    )
                ),
                new MatchArmNode(
                    rangeInSource: $this->range([0, 19], [0, 24]),
                    left: new ExpressionNodes(
                        new ExpressionNode(
                            rangeInSource: $this->range([0, 19], [0, 19]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 19], [0, 19]),
                                name: VariableName::from('d')
                            )
                        )
                    ),
                    right: new ExpressionNode(
                        rangeInSource: $this->range([0, 24], [0, 24]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 24], [0, 24]),
                                name: VariableName::from('e')
                            )
                    )
                ),
                new MatchArmNode(
                    rangeInSource: $this->range([0, 26], [0, 31]),
                    left: new ExpressionNodes(
                        new ExpressionNode(
                            rangeInSource: $this->range([0, 26], [0, 26]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 26], [0, 26]),
                                name: VariableName::from('f')
                            )
                        )
                    ),
                    right: new ExpressionNode(
                        rangeInSource: $this->range([0, 31], [0, 31]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 31], [0, 31]),
                                name: VariableName::from('g')
                            )
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedMatchNode,
            $matchParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesMatchWithOneSummarizedArm(): void
    {
        $matchParser = new MatchParser();
        $tokens = $this->createTokenIterator(
            'match (a) { b, c, d -> e }'
        );

        $expectedMatchNode = new MatchNode(
            rangeInSource: $this->range([0, 0], [0, 25]),
            subject: new ExpressionNode(
                rangeInSource: $this->range([0, 6], [0, 8]),
                root: new ValueReferenceNode(
                    rangeInSource: $this->range([0, 7], [0, 7]),
                    name: VariableName::from('a')
                )
            ),
            arms: new MatchArmNodes(
                new MatchArmNode(
                    rangeInSource: $this->range([0, 12], [0, 23]),
                    left: new ExpressionNodes(
                        new ExpressionNode(
                            rangeInSource: $this->range([0, 12], [0, 12]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 12], [0, 12]),
                                name: VariableName::from('b')
                            )
                        ),
                        new ExpressionNode(
                            rangeInSource: $this->range([0, 15], [0, 15]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 15], [0, 15]),
                                name: VariableName::from('c')
                            )
                        ),
                        new ExpressionNode(
                            rangeInSource: $this->range([0, 18], [0, 18]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 18], [0, 18]),
                                name: VariableName::from('d')
                            )
                        ),
                    ),
                    right: new ExpressionNode(
                        rangeInSource: $this->range([0, 23], [0, 23]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 23], [0, 23]),
                                name: VariableName::from('e')
                            )
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedMatchNode,
            $matchParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesMatchWithMultipleSummarizedArms(): void
    {
        $matchParser = new MatchParser();
        $tokens = $this->createTokenIterator(
            'match (a) { b, c, d -> e f, g, h -> i j, k, l -> m }'
        );

        $expectedMatchNode = new MatchNode(
            rangeInSource: $this->range([0, 0], [0, 51]),
            subject: new ExpressionNode(
                rangeInSource: $this->range([0, 6], [0, 8]),
                root: new ValueReferenceNode(
                    rangeInSource: $this->range([0, 7], [0, 7]),
                    name: VariableName::from('a')
                )
            ),
            arms: new MatchArmNodes(
                new MatchArmNode(
                    rangeInSource: $this->range([0, 12], [0, 23]),
                    left: new ExpressionNodes(
                        new ExpressionNode(
                            rangeInSource: $this->range([0, 12], [0, 12]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 12], [0, 12]),
                                name: VariableName::from('b')
                            )
                        ),
                        new ExpressionNode(
                            rangeInSource: $this->range([0, 15], [0, 15]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 15], [0, 15]),
                                name: VariableName::from('c')
                            )
                        ),
                        new ExpressionNode(
                            rangeInSource: $this->range([0, 18], [0, 18]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 18], [0, 18]),
                                name: VariableName::from('d')
                            )
                        ),
                    ),
                    right: new ExpressionNode(
                        rangeInSource: $this->range([0, 23], [0, 23]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 23], [0, 23]),
                                name: VariableName::from('e')
                            )
                    )
                ),
                new MatchArmNode(
                    rangeInSource: $this->range([0, 25], [0, 36]),
                    left: new ExpressionNodes(
                        new ExpressionNode(
                            rangeInSource: $this->range([0, 25], [0, 25]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 25], [0, 25]),
                                name: VariableName::from('f')
                            )
                        ),
                        new ExpressionNode(
                            rangeInSource: $this->range([0, 28], [0, 28]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 28], [0, 28]),
                                name: VariableName::from('g')
                            )
                        ),
                        new ExpressionNode(
                            rangeInSource: $this->range([0, 31], [0, 31]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 31], [0, 31]),
                                name: VariableName::from('h')
                            )
                        ),
                    ),
                    right: new ExpressionNode(
                        rangeInSource: $this->range([0, 36], [0, 36]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 36], [0, 36]),
                                name: VariableName::from('i')
                            )
                    )
                ),
                new MatchArmNode(
                    rangeInSource: $this->range([0, 38], [0, 49]),
                    left: new ExpressionNodes(
                        new ExpressionNode(
                            rangeInSource: $this->range([0, 38], [0, 38]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 38], [0, 38]),
                                name: VariableName::from('j')
                            )
                        ),
                        new ExpressionNode(
                            rangeInSource: $this->range([0, 41], [0, 41]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 41], [0, 41]),
                                name: VariableName::from('k')
                            )
                        ),
                        new ExpressionNode(
                            rangeInSource: $this->range([0, 44], [0, 44]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 44], [0, 44]),
                                name: VariableName::from('l')
                            )
                        ),
                    ),
                    right: new ExpressionNode(
                        rangeInSource: $this->range([0, 49], [0, 49]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 49], [0, 49]),
                                name: VariableName::from('m')
                            )
                    )
                ),
            )
        );

        $this->assertEquals(
            $expectedMatchNode,
            $matchParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesMatchWithOnlyDefaultArm(): void
    {
        $matchParser = new MatchParser();
        $tokens = $this->createTokenIterator(
            'match (a) { default -> b }'
        );

        $expectedMatchNode = new MatchNode(
            rangeInSource: $this->range([0, 0], [0, 25]),
            subject: new ExpressionNode(
                rangeInSource: $this->range([0, 6], [0, 8]),
                root: new ValueReferenceNode(
                    rangeInSource: $this->range([0, 7], [0, 7]),
                    name: VariableName::from('a')
                )
            ),
            arms: new MatchArmNodes(
                new MatchArmNode(
                    rangeInSource: $this->range([0, 12], [0, 23]),
                    left: null,
                    right: new ExpressionNode(
                        rangeInSource: $this->range([0, 23], [0, 23]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 23], [0, 23]),
                                name: VariableName::from('b')
                            )
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedMatchNode,
            $matchParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesMatchWithOneArmAndDefaultArm(): void
    {
        $matchParser = new MatchParser();
        $tokens = $this->createTokenIterator(
            'match (a) { b -> c default -> d }'
        );

        $expectedMatchNode = new MatchNode(
            rangeInSource: $this->range([0, 0], [0, 32]),
            subject: new ExpressionNode(
                rangeInSource: $this->range([0, 6], [0, 8]),
                root: new ValueReferenceNode(
                    rangeInSource: $this->range([0, 7], [0, 7]),
                    name: VariableName::from('a')
                )
            ),
            arms: new MatchArmNodes(
                new MatchArmNode(
                    rangeInSource: $this->range([0, 12], [0, 17]),
                    left: new ExpressionNodes(
                        new ExpressionNode(
                            rangeInSource: $this->range([0, 12], [0, 12]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 12], [0, 12]),
                                name: VariableName::from('b')
                            )
                        )
                    ),
                    right: new ExpressionNode(
                        rangeInSource: $this->range([0, 17], [0, 17]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 17], [0, 17]),
                                name: VariableName::from('c')
                            )
                    )
                ),
                new MatchArmNode(
                    rangeInSource: $this->range([0, 19], [0, 30]),
                    left: null,
                    right: new ExpressionNode(
                        rangeInSource: $this->range([0, 30], [0, 30]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 30], [0, 30]),
                                name: VariableName::from('d')
                            )
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedMatchNode,
            $matchParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesMatchWithOneSummarizedArmAndDefaultArm(): void
    {
        $matchParser = new MatchParser();
        $tokens = $this->createTokenIterator(
            'match (a) { b, c, d -> e default -> f }'
        );

        $expectedMatchNode = new MatchNode(
            rangeInSource: $this->range([0, 0], [0, 38]),
            subject: new ExpressionNode(
                rangeInSource: $this->range([0, 6], [0, 8]),
                root: new ValueReferenceNode(
                    rangeInSource: $this->range([0, 7], [0, 7]),
                    name: VariableName::from('a')
                )
            ),
            arms: new MatchArmNodes(
                new MatchArmNode(
                    rangeInSource: $this->range([0, 12], [0, 23]),
                    left: new ExpressionNodes(
                        new ExpressionNode(
                            rangeInSource: $this->range([0, 12], [0, 12]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 12], [0, 12]),
                                name: VariableName::from('b')
                            )
                        ),
                        new ExpressionNode(
                            rangeInSource: $this->range([0, 15], [0, 15]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 15], [0, 15]),
                                name: VariableName::from('c')
                            )
                        ),
                        new ExpressionNode(
                            rangeInSource: $this->range([0, 18], [0, 18]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 18], [0, 18]),
                                name: VariableName::from('d')
                            )
                        ),
                    ),
                    right: new ExpressionNode(
                        rangeInSource: $this->range([0, 23], [0, 23]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 23], [0, 23]),
                                name: VariableName::from('e')
                            )
                    )
                ),
                new MatchArmNode(
                    rangeInSource: $this->range([0, 25], [0, 36]),
                    left: null,
                    right: new ExpressionNode(
                        rangeInSource: $this->range([0, 36], [0, 36]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([0, 36], [0, 36]),
                                name: VariableName::from('f')
                            )
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedMatchNode,
            $matchParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesMatchWithMixedArms(): void
    {
        $matchParser = new MatchParser();
        $matchAsString = <<<AFX
        match (a) {
            b -> c
            d, e, f -> g
            default -> h
            i, j -> k
            l -> m
        }
        AFX;
        $tokens = $this->createTokenIterator($matchAsString);

        $expectedMatchNode = new MatchNode(
            rangeInSource: $this->range([0, 0], [6, 0]),
            subject: new ExpressionNode(
                rangeInSource: $this->range([0, 6], [0, 8]),
                root: new ValueReferenceNode(
                    rangeInSource: $this->range([0, 7], [0, 7]),
                    name: VariableName::from('a')
                )
            ),
            arms: new MatchArmNodes(
                new MatchArmNode(
                    rangeInSource: $this->range([1, 4], [1, 9]),
                    left: new ExpressionNodes(
                        new ExpressionNode(
                            rangeInSource: $this->range([1, 4], [1, 4]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([1, 4], [1, 4]),
                                name: VariableName::from('b')
                            )
                        ),
                    ),
                    right: new ExpressionNode(
                        rangeInSource: $this->range([1, 9], [1, 9]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([1, 9], [1, 9]),
                                name: VariableName::from('c')
                            )
                    )
                ),
                new MatchArmNode(
                    rangeInSource: $this->range([2, 4], [2, 15]),
                    left: new ExpressionNodes(
                        new ExpressionNode(
                            rangeInSource: $this->range([2, 4], [2, 4]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([2, 4], [2, 4]),
                                name: VariableName::from('d')
                            )
                        ),
                        new ExpressionNode(
                            rangeInSource: $this->range([2, 7], [2, 7]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([2, 7], [2, 7]),
                                name: VariableName::from('e')
                            )
                        ),
                        new ExpressionNode(
                            rangeInSource: $this->range([2, 10], [2, 10]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([2, 10], [2, 10]),
                                name: VariableName::from('f')
                            )
                        ),
                    ),
                    right: new ExpressionNode(
                        rangeInSource: $this->range([2, 15], [2, 15]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([2, 15], [2, 15]),
                                name: VariableName::from('g')
                            )
                    )
                ),
                new MatchArmNode(
                    rangeInSource: $this->range([3, 4], [3, 15]),
                    left: null,
                    right: new ExpressionNode(
                        rangeInSource: $this->range([3, 15], [3, 15]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([3, 15], [3, 15]),
                                name: VariableName::from('h')
                            )
                    )
                ),
                new MatchArmNode(
                    rangeInSource: $this->range([4, 4], [4, 12]),
                    left: new ExpressionNodes(
                        new ExpressionNode(
                            rangeInSource: $this->range([4, 4], [4, 4]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([4, 4], [4, 4]),
                                name: VariableName::from('i')
                            )
                        ),
                        new ExpressionNode(
                            rangeInSource: $this->range([4, 7], [4, 7]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([4, 7], [4, 7]),
                                name: VariableName::from('j')
                            )
                        ),
                    ),
                    right: new ExpressionNode(
                        rangeInSource: $this->range([4, 12], [4, 12]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([4, 12], [4, 12]),
                                name: VariableName::from('k')
                            )
                    )
                ),
                new MatchArmNode(
                    rangeInSource: $this->range([5, 4], [5, 9]),
                    left: new ExpressionNodes(
                        new ExpressionNode(
                            rangeInSource: $this->range([5, 4], [5, 4]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([5, 4], [5, 4]),
                                name: VariableName::from('l')
                            )
                        ),
                    ),
                    right: new ExpressionNode(
                        rangeInSource: $this->range([5, 9], [5, 9]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([5, 9], [5, 9]),
                                name: VariableName::from('m')
                            )
                    )
                ),
            )
        );

        $this->assertEquals(
            $expectedMatchNode,
            $matchParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesNestedMatchAsSubject(): void
    {
        $matchParser = new MatchParser();
        $matchAsString = <<<AFX
        match (match (a) { b, c -> d default -> e }) {
            d, e -> f
            default -> g
        }
        AFX;
        $tokens = $this->createTokenIterator($matchAsString);

        $expectedMatchNode = new MatchNode(
            rangeInSource: $this->range([0, 0], [3, 0]),
            subject: new ExpressionNode(
                rangeInSource: $this->range([0, 6], [0, 43]),
                root: new MatchNode(
                    rangeInSource: $this->range([0, 7], [0, 42]),
                    subject: new ExpressionNode(
                        rangeInSource: $this->range([0, 13], [0, 15]),
                        root: new ValueReferenceNode(
                            rangeInSource: $this->range([0, 14], [0, 14]),
                            name: VariableName::from('a')
                        )
                    ),
                    arms: new MatchArmNodes(
                        new MatchArmNode(
                            rangeInSource: $this->range([0, 19], [0, 27]),
                            left: new ExpressionNodes(
                                new ExpressionNode(
                                    rangeInSource: $this->range([0, 19], [0, 19]),
                                    root: new ValueReferenceNode(
                                        rangeInSource: $this->range([0, 19], [0, 19]),
                                        name: VariableName::from('b')
                                    )
                                ),
                                new ExpressionNode(
                                    rangeInSource: $this->range([0, 22], [0, 22]),
                                    root: new ValueReferenceNode(
                                        rangeInSource: $this->range([0, 22], [0, 22]),
                                        name: VariableName::from('c')
                                    )
                                )
                            ),
                            right: new ExpressionNode(
                                rangeInSource: $this->range([0, 27], [0, 27]),
                                    root: new ValueReferenceNode(
                                        rangeInSource: $this->range([0, 27], [0, 27]),
                                        name: VariableName::from('d')
                                    )
                            )
                        ),
                        new MatchArmNode(
                            rangeInSource: $this->range([0, 29], [0, 40]),
                            left: null,
                            right: new ExpressionNode(
                                rangeInSource: $this->range([0, 40], [0, 40]),
                                root: new ValueReferenceNode(
                                    rangeInSource: $this->range([0, 40], [0, 40]),
                                    name: VariableName::from('e')
                                )
                            )
                        ),
                    )
                )
            ),
            arms: new MatchArmNodes(
                new MatchArmNode(
                    rangeInSource: $this->range([1, 4], [1, 12]),
                    left: new ExpressionNodes(
                        new ExpressionNode(
                            rangeInSource: $this->range([1, 4], [1, 4]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([1, 4], [1, 4]),
                                name: VariableName::from('d')
                            )
                            ),
                        new ExpressionNode(
                            rangeInSource: $this->range([1, 7], [1, 7]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([1, 7], [1, 7]),
                                name: VariableName::from('e')
                            )
                        )
                    ),
                    right: new ExpressionNode(
                        rangeInSource: $this->range([1, 12], [1, 12]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([1, 12], [1, 12]),
                                name: VariableName::from('f')
                            )
                    )
                ),
                new MatchArmNode(
                    rangeInSource: $this->range([2, 4], [2, 15]),
                    left: null,
                    right: new ExpressionNode(
                        rangeInSource: $this->range([2, 15], [2, 15]),
                        root: new ValueReferenceNode(
                            rangeInSource: $this->range([2, 15], [2, 15]),
                            name: VariableName::from('g')
                        )
                    )
                ),
            )
        );

        $this->assertEquals(
            $expectedMatchNode,
            $matchParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesNestedMatchAsArmLeft(): void
    {
        $matchParser = new MatchParser();
        $matchAsString = <<<AFX
        match (a) {
            match (b) { c, d -> e default -> f } -> g
            match (h) { i -> j },
            match (k) { l, m -> n default -> o } -> p
            default -> q
        }
        AFX;
        $tokens = $this->createTokenIterator($matchAsString);

        $expectedMatchNode = new MatchNode(
            rangeInSource: $this->range([0, 0], [5, 0]),
            subject: new ExpressionNode(
                rangeInSource: $this->range([0, 6], [0, 8]),
                root: new ValueReferenceNode(
                    rangeInSource: $this->range([0, 7], [0, 7]),
                    name: VariableName::from('a')
                )
            ),
            arms: new MatchArmNodes(
                new MatchArmNode(
                    rangeInSource: $this->range([1, 4], [1, 44]),
                    left: new ExpressionNodes(
                        new ExpressionNode(
                            rangeInSource: $this->range([1, 4], [1, 39]),
                            root: new MatchNode(
                                rangeInSource: $this->range([1, 4], [1, 39]),
                                subject: new ExpressionNode(
                                    rangeInSource: $this->range([1, 10], [1, 12]),
                                    root: new ValueReferenceNode(
                                        rangeInSource: $this->range([1, 11], [1, 11]),
                                        name: VariableName::from('b')
                                    )
                                ),
                                arms: new MatchArmNodes(
                                    new MatchArmNode(
                                        rangeInSource: $this->range([1, 16], [1, 24]),
                                        left: new ExpressionNodes(
                                            new ExpressionNode(
                                                rangeInSource: $this->range([1, 16], [1, 16]),
                                                root: new ValueReferenceNode(
                                                    rangeInSource: $this->range([1, 16], [1, 16]),
                                                    name: VariableName::from('c')
                                                )
                                            ),
                                            new ExpressionNode(
                                                rangeInSource: $this->range([1, 19], [1, 19]),
                                                root: new ValueReferenceNode(
                                                    rangeInSource: $this->range([1, 19], [1, 19]),
                                                    name: VariableName::from('d')
                                                )
                                            ),
                                        ),
                                        right: new ExpressionNode(
                                            rangeInSource: $this->range([1, 24], [1, 24]),
                                            root: new ValueReferenceNode(
                                                rangeInSource: $this->range([1, 24], [1, 24]),
                                                name: VariableName::from('e')
                                            )
                                        )
                                    ),
                                    new MatchArmNode(
                                        rangeInSource: $this->range([1, 26], [1, 37]),
                                        left: null,
                                        right: new ExpressionNode(
                                            rangeInSource: $this->range([1, 37], [1, 37]),
                                            root: new ValueReferenceNode(
                                                rangeInSource: $this->range([1, 37], [1, 37]),
                                                name: VariableName::from('f')
                                            )
                                        )
                                    ),
                                )
                            )
                        )
                    ),
                    right: new ExpressionNode(
                        rangeInSource: $this->range([1, 44], [1, 44]),
                        root: new ValueReferenceNode(
                            rangeInSource: $this->range([1, 44], [1, 44]),
                            name: VariableName::from('g')
                        )
                    )
                ),
                new MatchArmNode(
                    rangeInSource: $this->range([2, 4], [3, 44]),
                    left: new ExpressionNodes(
                        new ExpressionNode(
                            rangeInSource: $this->range([2, 4], [2, 23]),
                            root: new MatchNode(
                                rangeInSource: $this->range([2, 4], [2, 23]),
                                subject: new ExpressionNode(
                                    rangeInSource: $this->range([2, 10], [2, 12]),
                                    root: new ValueReferenceNode(
                                        rangeInSource: $this->range([2, 11], [2, 11]),
                                        name: VariableName::from('h')
                                    )
                                ),
                                arms: new MatchArmNodes(
                                    new MatchArmNode(
                                        rangeInSource: $this->range([2, 16], [2, 21]),
                                        left: new ExpressionNodes(
                                            new ExpressionNode(
                                                rangeInSource: $this->range([2, 16], [2, 16]),
                                                root: new ValueReferenceNode(
                                                    rangeInSource: $this->range([2, 16], [2, 16]),
                                                    name: VariableName::from('i')
                                                )
                                            ),
                                        ),
                                        right: new ExpressionNode(
                                            rangeInSource: $this->range([2, 21], [2, 21]),
                                            root: new ValueReferenceNode(
                                                rangeInSource: $this->range([2, 21], [2, 21]),
                                                name: VariableName::from('j')
                                            )
                                        )
                                    ),
                                )
                            )
                        ),
                        new ExpressionNode(
                            rangeInSource: $this->range([3, 4], [3, 39]),
                            root: new MatchNode(
                                rangeInSource: $this->range([3, 4], [3, 39]),
                                subject: new ExpressionNode(
                                    rangeInSource: $this->range([3, 10], [3, 12]),
                                    root: new ValueReferenceNode(
                                        rangeInSource: $this->range([3, 11], [3, 11]),
                                        name: VariableName::from('k')
                                    )
                                ),
                                arms: new MatchArmNodes(
                                    new MatchArmNode(
                                        rangeInSource: $this->range([3, 16], [3, 24]),
                                        left: new ExpressionNodes(
                                            new ExpressionNode(
                                                rangeInSource: $this->range([3, 16], [3, 16]),
                                                root: new ValueReferenceNode(
                                                    rangeInSource: $this->range([3, 16], [3, 16]),
                                                    name: VariableName::from('l')
                                                )
                                            ),
                                            new ExpressionNode(
                                                rangeInSource: $this->range([3, 19], [3, 19]),
                                                root: new ValueReferenceNode(
                                                    rangeInSource: $this->range([3, 19], [3, 19]),
                                                    name: VariableName::from('m')
                                                )
                                            ),
                                        ),
                                        right: new ExpressionNode(
                                            rangeInSource: $this->range([3, 24], [3, 24]),
                                            root: new ValueReferenceNode(
                                                rangeInSource: $this->range([3, 24], [3, 24]),
                                                name: VariableName::from('n')
                                            )
                                        )
                                    ),
                                    new MatchArmNode(
                                        rangeInSource: $this->range([3, 26], [3, 37]),
                                        left: null,
                                        right: new ExpressionNode(
                                            rangeInSource: $this->range([3, 37], [3, 37]),
                                            root: new ValueReferenceNode(
                                                rangeInSource: $this->range([3, 37], [3, 37]),
                                                name: VariableName::from('o')
                                            )
                                        )
                                    ),
                                )
                            )
                        ),
                    ),
                    right: new ExpressionNode(
                        rangeInSource: $this->range([3, 44], [3, 44]),
                        root: new ValueReferenceNode(
                            rangeInSource: $this->range([3, 44], [3, 44]),
                            name: VariableName::from('p')
                        )
                    )
                ),
                new MatchArmNode(
                    rangeInSource: $this->range([4, 4], [4, 15]),
                    left: null,
                    right: new ExpressionNode(
                        rangeInSource: $this->range([4, 15], [4, 15]),
                        root: new ValueReferenceNode(
                            rangeInSource: $this->range([4, 15], [4, 15]),
                            name: VariableName::from('q')
                        )
                    )
                ),
            )
        );

        $this->assertEquals(
            $expectedMatchNode,
            $matchParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesNestedMatchAsArmRight(): void
    {
        $matchParser = new MatchParser();
        $matchAsString = <<<AFX
        match (a) {
            b -> match (c) { d, e -> f default -> g }
            default -> h
        }
        AFX;
        $tokens = $this->createTokenIterator($matchAsString);

        $expectedMatchNode = new MatchNode(
            rangeInSource: $this->range([0, 0], [3, 0]),
            subject: new ExpressionNode(
                rangeInSource: $this->range([0, 6], [0, 8]),
                root: new ValueReferenceNode(
                    rangeInSource: $this->range([0, 7], [0, 7]),
                    name: VariableName::from('a')
                )
            ),
            arms: new MatchArmNodes(
                new MatchArmNode(
                    rangeInSource: $this->range([1, 4], [1, 44]),
                    left: new ExpressionNodes(
                        new ExpressionNode(
                            rangeInSource: $this->range([1, 4], [1, 4]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([1, 4], [1, 4]),
                                name: VariableName::from('b')
                            )
                        ),
                    ),
                    right: new ExpressionNode(
                        rangeInSource: $this->range([1, 9], [1, 44]),
                        root: new MatchNode(
                            rangeInSource: $this->range([1, 9], [1, 44]),
                            subject: new ExpressionNode(
                                rangeInSource: $this->range([1, 15], [1, 17]),
                                root: new ValueReferenceNode(
                                    rangeInSource: $this->range([1, 16], [1, 16]),
                                    name: VariableName::from('c')
                                )
                            ),
                            arms: new MatchArmNodes(
                                new MatchArmNode(
                                    rangeInSource: $this->range([1, 21], [1, 29]),
                                    left: new ExpressionNodes(
                                        new ExpressionNode(
                                            rangeInSource: $this->range([1, 21], [1, 21]),
                                            root: new ValueReferenceNode(
                                                rangeInSource: $this->range([1, 21], [1, 21]),
                                                name: VariableName::from('d')
                                            )
                                        ),
                                        new ExpressionNode(
                                            rangeInSource: $this->range([1, 24], [1, 24]),
                                            root: new ValueReferenceNode(
                                                rangeInSource: $this->range([1, 24], [1, 24]),
                                                name: VariableName::from('e')
                                            )
                                        )
                                    ),
                                    right: new ExpressionNode(
                                        rangeInSource: $this->range([1, 29], [1, 29]),
                                        root: new ValueReferenceNode(
                                            rangeInSource: $this->range([1, 29], [1, 29]),
                                            name: VariableName::from('f')
                                        )
                                    )
                                ),
                                new MatchArmNode(
                                    rangeInSource: $this->range([1, 31], [1, 42]),
                                    left: null,
                                    right: new ExpressionNode(
                                        rangeInSource: $this->range([1, 42], [1, 42]),
                                        root: new ValueReferenceNode(
                                            rangeInSource: $this->range([1, 42], [1, 42]),
                                            name: VariableName::from('g')
                                        )
                                    )
                                ),
                            )
                        )
                    )
                ),
                new MatchArmNode(
                    rangeInSource: $this->range([2, 4], [2, 15]),
                    left: null,
                    right: new ExpressionNode(
                        rangeInSource: $this->range([2, 15], [2, 15]),
                        root: new ValueReferenceNode(
                            rangeInSource: $this->range([2, 15], [2, 15]),
                            name: VariableName::from('h')
                        )
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedMatchNode,
            $matchParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function emptyMatchArmsAreNotAllowed(): void
    {
        $matchParser = new MatchParser();
        $tokens = $this->createTokenIterator('match (a) {}');

        $this->expectException(ParserException::class);
        $this->expectExceptionObject(
            MatchCouldNotBeParsed::becauseOfInvalidMatchArmNodes(
                cause: InvalidMatchArmNodes::becauseTheyWereEmpty(),
                affectedRangeInSource: $this->range([0, 0], [0, 4])
            )
        );

        $matchParser->parse($tokens);
    }

    /**
     * @test
     */
    public function multipleDefaultArmsAreNotAllowed(): void
    {
        $matchParser = new MatchParser();
        $matchAsString = <<<AFX
        match (a) {
            b, c -> d
            default -> e
            f, g -> h
            default -> i
            j -> k
        }
        AFX;
        $tokens = $this->createTokenIterator($matchAsString);

        $this->expectException(ParserException::class);
        $this->expectExceptionObject(
            MatchCouldNotBeParsed::becauseOfInvalidMatchArmNodes(
                cause: InvalidMatchArmNodes::becauseTheyContainMoreThanOneDefaultMatchArmNode(
                    secondDefaultMatchArmNode: new MatchArmNode(
                        rangeInSource: $this->range([4, 4], [4, 15]),
                        left: null,
                        right: new ExpressionNode(
                            rangeInSource: $this->range([4, 15], [4, 15]),
                            root: new ValueReferenceNode(
                                rangeInSource: $this->range([4, 15], [4, 15]),
                                name: VariableName::from('i')
                            )
                        )
                    )
                )
            )
        );

        $matchParser->parse($tokens);
    }
}
