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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Parser\EnumDeclaration;

use PackageFactory\ComponentEngine\Domain\EnumMemberName\EnumMemberName;
use PackageFactory\ComponentEngine\Domain\EnumName\EnumName;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumMemberDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumMemberDeclarationNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumMemberNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumMemberValueNode;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumNameNode;
use PackageFactory\ComponentEngine\Language\AST\Node\IntegerLiteral\IntegerFormat;
use PackageFactory\ComponentEngine\Language\AST\Node\IntegerLiteral\IntegerLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StringLiteral\StringLiteralNode;
use PackageFactory\ComponentEngine\Language\Parser\EnumDeclaration\EnumDeclarationParser;
use PackageFactory\ComponentEngine\Test\Unit\Language\Parser\ParserTestCase;

final class EnumDeclarationParserTest extends ParserTestCase
{
    /**
     * @test
     */
    public function parsesEnumDeclarationWithOneValuelessMember(): void
    {
        $enumDeclarationParser = new EnumDeclarationParser();
        $tokens = $this->createTokenIterator('enum Foo { BAR }');

        $expectedEnumDeclarationNode = new EnumDeclarationNode(
            rangeInSource: $this->range([0, 0], [0, 15]),
            name: new EnumNameNode(
                rangeInSource: $this->range([0, 5], [0, 7]),
                value: EnumName::from('Foo')
            ),
            members: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([0, 11], [0, 13]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([0, 11], [0, 13]),
                        value: EnumMemberName::from('BAR')
                    ),
                    value: null
                )
            )
        );

        $this->assertEquals(
            $expectedEnumDeclarationNode,
            $enumDeclarationParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesEnumDeclarationWithThreeValuelessMembers(): void
    {
        $enumDeclarationParser = new EnumDeclarationParser();
        $tokens = $this->createTokenIterator('enum Foo { BAR BAZ QUX }');

        $expectedEnumDeclarationNode = new EnumDeclarationNode(
            rangeInSource: $this->range([0, 0], [0, 23]),
            name: new EnumNameNode(
                rangeInSource: $this->range([0, 5], [0, 7]),
                value: EnumName::from('Foo')
            ),
            members: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([0, 11], [0, 13]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([0, 11], [0, 13]),
                        value: EnumMemberName::from('BAR')
                    ),
                    value: null
                ),
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([0, 15], [0, 17]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([0, 15], [0, 17]),
                        value: EnumMemberName::from('BAZ')
                    ),
                    value: null
                ),
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([0, 19], [0, 21]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([0, 19], [0, 21]),
                        value: EnumMemberName::from('QUX')
                    ),
                    value: null
                )
            )
        );

        $this->assertEquals(
            $expectedEnumDeclarationNode,
            $enumDeclarationParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesEnumDeclarationWithOneStringValueMember(): void
    {
        $enumDeclarationParser = new EnumDeclarationParser();
        $tokens = $this->createTokenIterator('enum Foo { BAR("BAR") }');

        $expectedEnumDeclarationNode = new EnumDeclarationNode(
            rangeInSource: $this->range([0, 0], [0, 22]),
            name: new EnumNameNode(
                rangeInSource: $this->range([0, 5], [0, 7]),
                value: EnumName::from('Foo')
            ),
            members: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([0, 11], [0, 20]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([0, 11], [0, 13]),
                        value: EnumMemberName::from('BAR')
                    ),
                    value: new EnumMemberValueNode(
                        rangeInSource: $this->range([0, 14], [0, 20]),
                        value: new StringLiteralNode(
                            rangeInSource: $this->range([0, 16], [0, 18]),
                            value: 'BAR'
                        )
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedEnumDeclarationNode,
            $enumDeclarationParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesEnumDeclarationWithSevenStringValueMembers(): void
    {
        $enumDeclarationParser = new EnumDeclarationParser();
        $enumAsString = <<<AFX
        enum Weekday {
            MONDAY("mon")
            TUESDAY("tue")
            WEDNESDAY("wed")
            THURSDAY("thu")
            FRIDAY("fri")
            SATURDAY("sat")
            SUNDAY("sun")
        }
        AFX;
        $tokens = $this->createTokenIterator($enumAsString);

        $expectedEnumDeclarationNode = new EnumDeclarationNode(
            rangeInSource: $this->range([0, 0], [8, 0]),
            name: new EnumNameNode(
                rangeInSource: $this->range([0, 5], [0, 11]),
                value: EnumName::from('Weekday')
            ),
            members: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([1, 4], [1, 16]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([1, 4], [1, 9]),
                        value: EnumMemberName::from('MONDAY')
                    ),
                    value: new EnumMemberValueNode(
                        rangeInSource: $this->range([1, 10], [1, 16]),
                        value: new StringLiteralNode(
                            rangeInSource: $this->range([1, 12], [1, 14]),
                            value: 'mon'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([2, 4], [2, 17]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([2, 4], [2, 10]),
                        value: EnumMemberName::from('TUESDAY')
                    ),
                    value: new EnumMemberValueNode(
                        rangeInSource: $this->range([2, 11], [2, 17]),
                        value: new StringLiteralNode(
                            rangeInSource: $this->range([2, 13], [2, 15]),
                            value: 'tue'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([3, 4], [3, 19]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([3, 4], [3, 12]),
                        value: EnumMemberName::from('WEDNESDAY')
                    ),
                    value: new EnumMemberValueNode(
                        rangeInSource: $this->range([3, 13], [3, 19]),
                        value: new StringLiteralNode(
                            rangeInSource: $this->range([3, 15], [3, 17]),
                            value: 'wed'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([4, 4], [4, 18]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([4, 4], [4, 11]),
                        value: EnumMemberName::from('THURSDAY')
                    ),
                    value: new EnumMemberValueNode(
                        rangeInSource: $this->range([4, 12], [4, 18]),
                        value: new StringLiteralNode(
                            rangeInSource: $this->range([4, 14], [4, 16]),
                            value: 'thu'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([5, 4], [5, 16]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([5, 4], [5, 9]),
                        value: EnumMemberName::from('FRIDAY')
                    ),
                    value: new EnumMemberValueNode(
                        rangeInSource: $this->range([5, 10], [5, 16]),
                        value: new StringLiteralNode(
                            rangeInSource: $this->range([5, 12], [5, 14]),
                            value: 'fri'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([6, 4], [6, 18]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([6, 4], [6, 11]),
                        value: EnumMemberName::from('SATURDAY')
                    ),
                    value: new EnumMemberValueNode(
                        rangeInSource: $this->range([6, 12], [6, 18]),
                        value: new StringLiteralNode(
                            rangeInSource: $this->range([6, 14], [6, 16]),
                            value: 'sat'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([7, 4], [7, 16]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([7, 4], [7, 9]),
                        value: EnumMemberName::from('SUNDAY')
                    ),
                    value: new EnumMemberValueNode(
                        rangeInSource: $this->range([7, 10], [7, 16]),
                        value: new StringLiteralNode(
                            rangeInSource: $this->range([7, 12], [7, 14]),
                            value: 'sun'
                        )
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedEnumDeclarationNode,
            $enumDeclarationParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesEnumDeclarationWithOneBinaryIntegerValueMember(): void
    {
        $enumDeclarationParser = new EnumDeclarationParser();
        $tokens = $this->createTokenIterator('enum Foo { BAR(0b101) }');

        $expectedEnumDeclarationNode = new EnumDeclarationNode(
            rangeInSource: $this->range([0, 0], [0, 22]),
            name: new EnumNameNode(
                rangeInSource: $this->range([0, 5], [0, 7]),
                value: EnumName::from('Foo')
            ),
            members: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([0, 11], [0, 20]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([0, 11], [0, 13]),
                        value: EnumMemberName::from('BAR')
                    ),
                    value: new EnumMemberValueNode(
                        rangeInSource: $this->range([0, 14], [0, 20]),
                        value: new IntegerLiteralNode(
                            rangeInSource: $this->range([0, 15], [0, 19]),
                            format: IntegerFormat::BINARY,
                            value: '0b101'
                        )
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedEnumDeclarationNode,
            $enumDeclarationParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesEnumDeclarationWithOneOctalIntegerValueMember(): void
    {
        $enumDeclarationParser = new EnumDeclarationParser();
        $tokens = $this->createTokenIterator('enum Foo { BAR(0o644) }');

        $expectedEnumDeclarationNode = new EnumDeclarationNode(
            rangeInSource: $this->range([0, 0], [0, 22]),
            name: new EnumNameNode(
                rangeInSource: $this->range([0, 5], [0, 7]),
                value: EnumName::from('Foo')
            ),
            members: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([0, 11], [0, 20]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([0, 11], [0, 13]),
                        value: EnumMemberName::from('BAR')
                    ),
                    value: new EnumMemberValueNode(
                        rangeInSource: $this->range([0, 14], [0, 20]),
                        value: new IntegerLiteralNode(
                            rangeInSource: $this->range([0, 15], [0, 19]),
                            format: IntegerFormat::OCTAL,
                            value: '0o644'
                        )
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedEnumDeclarationNode,
            $enumDeclarationParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesEnumDeclarationWithOneDecimalIntegerValueMember(): void
    {
        $enumDeclarationParser = new EnumDeclarationParser();
        $tokens = $this->createTokenIterator('enum Foo { BAR(42) }');

        $expectedEnumDeclarationNode = new EnumDeclarationNode(
            rangeInSource: $this->range([0, 0], [0, 19]),
            name: new EnumNameNode(
                rangeInSource: $this->range([0, 5], [0, 7]),
                value: EnumName::from('Foo')
            ),
            members: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([0, 11], [0, 17]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([0, 11], [0, 13]),
                        value: EnumMemberName::from('BAR')
                    ),
                    value: new EnumMemberValueNode(
                        rangeInSource: $this->range([0, 14], [0, 17]),
                        value: new IntegerLiteralNode(
                            rangeInSource: $this->range([0, 15], [0, 16]),
                            format: IntegerFormat::DECIMAL,
                            value: '42'
                        )
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedEnumDeclarationNode,
            $enumDeclarationParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesEnumDeclarationWithOneHexadecimalIntegerValueMember(): void
    {
        $enumDeclarationParser = new EnumDeclarationParser();
        $tokens = $this->createTokenIterator('enum Foo { BAR(0xABC) }');

        $expectedEnumDeclarationNode = new EnumDeclarationNode(
            rangeInSource: $this->range([0, 0], [0, 22]),
            name: new EnumNameNode(
                rangeInSource: $this->range([0, 5], [0, 7]),
                value: EnumName::from('Foo')
            ),
            members: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([0, 11], [0, 20]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([0, 11], [0, 13]),
                        value: EnumMemberName::from('BAR')
                    ),
                    value: new EnumMemberValueNode(
                        rangeInSource: $this->range([0, 14], [0, 20]),
                        value: new IntegerLiteralNode(
                            rangeInSource: $this->range([0, 15], [0, 19]),
                            format: IntegerFormat::HEXADECIMAL,
                            value: '0xABC'
                        )
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedEnumDeclarationNode,
            $enumDeclarationParser->parse($tokens)
        );
    }

    /**
     * @test
     */
    public function parsesEnumDeclarationWithwelveIntegerValueMembers(): void
    {
        $enumDeclarationParser = new EnumDeclarationParser();
        $enumAsString = <<<AFX
        enum Month {
            JANUARY(1)
            FEBRUARY(2)
            MARCH(3)
            APRIL(4)
            MAY(5)
            JUNE(6)
            JULY(7)
            AUGUST(8)
            SEPTEMBER(9)
            OCTOBER(10)
            NOVEMBER(11)
            DECEMBER(12)
        }
        AFX;
        $tokens = $this->createTokenIterator($enumAsString);

        $expectedEnumDeclarationNode = new EnumDeclarationNode(
            rangeInSource: $this->range([0, 0], [13, 0]),
            name: new EnumNameNode(
                rangeInSource: $this->range([0, 5], [0, 9]),
                value: EnumName::from('Month')
            ),
            members: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([1, 4], [1, 13]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([1, 4], [1, 10]),
                        value: EnumMemberName::from('JANUARY')
                    ),
                    value: new EnumMemberValueNode(
                        rangeInSource: $this->range([1, 11], [1, 13]),
                        value: new IntegerLiteralNode(
                            rangeInSource: $this->range([1, 12], [1, 12]),
                            format: IntegerFormat::DECIMAL,
                            value: '1'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([2, 4], [2, 14]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([2, 4], [2, 11]),
                        value: EnumMemberName::from('FEBRUARY')
                    ),
                    value: new EnumMemberValueNode(
                        rangeInSource: $this->range([2, 12], [2, 14]),
                        value: new IntegerLiteralNode(
                            rangeInSource: $this->range([2, 13], [2, 13]),
                            format: IntegerFormat::DECIMAL,
                            value: '2'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([3, 4], [3, 11]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([3, 4], [3, 8]),
                        value: EnumMemberName::from('MARCH')
                    ),
                    value: new EnumMemberValueNode(
                        rangeInSource: $this->range([3, 9], [3, 11]),
                        value: new IntegerLiteralNode(
                            rangeInSource: $this->range([3, 10], [3, 10]),
                            format: IntegerFormat::DECIMAL,
                            value: '3'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([4, 4], [4, 11]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([4, 4], [4, 8]),
                        value: EnumMemberName::from('APRIL')
                    ),
                    value: new EnumMemberValueNode(
                        rangeInSource: $this->range([4, 9], [4, 11]),
                        value: new IntegerLiteralNode(
                            rangeInSource: $this->range([4, 10], [4, 10]),
                            format: IntegerFormat::DECIMAL,
                            value: '4'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([5, 4], [5, 9]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([5, 4], [5, 6]),
                        value: EnumMemberName::from('MAY')
                    ),
                    value: new EnumMemberValueNode(
                        rangeInSource: $this->range([5, 7], [5, 9]),
                        value: new IntegerLiteralNode(
                            rangeInSource: $this->range([5, 8], [5, 8]),
                            format: IntegerFormat::DECIMAL,
                            value: '5'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([6, 4], [6, 10]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([6, 4], [6, 7]),
                        value: EnumMemberName::from('JUNE')
                    ),
                    value: new EnumMemberValueNode(
                        rangeInSource: $this->range([6, 8], [6, 10]),
                        value: new IntegerLiteralNode(
                            rangeInSource: $this->range([6, 9], [6, 9]),
                            format: IntegerFormat::DECIMAL,
                            value: '6'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([7, 4], [7, 10]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([7, 4], [7, 7]),
                        value: EnumMemberName::from('JULY')
                    ),
                    value: new EnumMemberValueNode(
                        rangeInSource: $this->range([7, 8], [7, 10]),
                        value: new IntegerLiteralNode(
                            rangeInSource: $this->range([7, 9], [7, 9]),
                            format: IntegerFormat::DECIMAL,
                            value: '7'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([8, 4], [8, 12]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([8, 4], [8, 9]),
                        value: EnumMemberName::from('AUGUST')
                    ),
                    value: new EnumMemberValueNode(
                        rangeInSource: $this->range([8, 10], [8, 12]),
                        value: new IntegerLiteralNode(
                            rangeInSource: $this->range([8, 11], [8, 11]),
                            format: IntegerFormat::DECIMAL,
                            value: '8'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([9, 4], [9, 15]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([9, 4], [9, 12]),
                        value: EnumMemberName::from('SEPTEMBER')
                    ),
                    value: new EnumMemberValueNode(
                        rangeInSource: $this->range([9, 13], [9, 15]),
                        value: new IntegerLiteralNode(
                            rangeInSource: $this->range([9, 14], [9, 14]),
                            format: IntegerFormat::DECIMAL,
                            value: '9'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([10, 4], [10, 14]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([10, 4], [10, 10]),
                        value: EnumMemberName::from('OCTOBER')
                    ),
                    value: new EnumMemberValueNode(
                        rangeInSource: $this->range([10, 11], [10, 14]),
                        value: new IntegerLiteralNode(
                            rangeInSource: $this->range([10, 12], [10, 13]),
                            format: IntegerFormat::DECIMAL,
                            value: '10'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([11, 4], [11, 15]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([11, 4], [11, 11]),
                        value: EnumMemberName::from('NOVEMBER')
                    ),
                    value: new EnumMemberValueNode(
                        rangeInSource: $this->range([11, 12], [11, 15]),
                        value: new IntegerLiteralNode(
                            rangeInSource: $this->range([11, 13], [11, 14]),
                            format: IntegerFormat::DECIMAL,
                            value: '11'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    rangeInSource: $this->range([12, 4], [12, 15]),
                    name: new EnumMemberNameNode(
                        rangeInSource: $this->range([12, 4], [12, 11]),
                        value: EnumMemberName::from('DECEMBER')
                    ),
                    value: new EnumMemberValueNode(
                        rangeInSource: $this->range([12, 12], [12, 15]),
                        value: new IntegerLiteralNode(
                            rangeInSource: $this->range([12, 13], [12, 14]),
                            format: IntegerFormat::DECIMAL,
                            value: '12'
                        )
                    )
                )
            )
        );

        $this->assertEquals(
            $expectedEnumDeclarationNode,
            $enumDeclarationParser->parse($tokens)
        );
    }
}
