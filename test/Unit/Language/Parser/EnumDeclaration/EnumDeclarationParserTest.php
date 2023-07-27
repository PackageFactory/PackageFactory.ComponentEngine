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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Parser\EnumDeclaration;

use PackageFactory\ComponentEngine\Language\AST\EnumDeclaration\EnumDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\EnumDeclaration\EnumMemberDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\EnumDeclaration\EnumMemberDeclarationNodes;
use PackageFactory\ComponentEngine\Language\AST\EnumDeclaration\EnumMemberName;
use PackageFactory\ComponentEngine\Language\AST\EnumDeclaration\EnumName;
use PackageFactory\ComponentEngine\Language\AST\IntegerLiteral\IntegerFormat;
use PackageFactory\ComponentEngine\Language\AST\IntegerLiteral\IntegerLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\StringLiteral\StringLiteralNode;
use PackageFactory\ComponentEngine\Language\Parser\EnumDeclaration\EnumDeclarationParser;
use PackageFactory\ComponentEngine\Language\Shared\Location\Location;
use PackageFactory\ComponentEngine\Parser\Source\Boundaries;
use PackageFactory\ComponentEngine\Parser\Source\Path;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Tokenizer;
use PHPUnit\Framework\TestCase;

final class EnumDeclarationParserTest extends TestCase
{
    /**
     * @test
     */
    public function oneValuelessMember(): void
    {
        $enumDeclarationParser = new EnumDeclarationParser();
        $tokens = Tokenizer::fromSource(Source::fromString('enum Foo { BAR }'))->getIterator();

        $expectedEnumDeclarationNode = new EnumDeclarationNode(
            location: new Location(
                sourcePath: Path::fromString(':memory:'),
                boundaries: Boundaries::fromPositions(
                    Position::create(0, 0, 0),
                    Position::create(15, 0, 15)
                )
            ),
            enumName: EnumName::from('Foo'),
            memberDeclarations: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    location: new Location(
                        sourcePath: Path::fromString(':memory:'),
                        boundaries: Boundaries::fromPositions(
                            Position::create(11, 0, 11),
                            Position::create(13, 0, 13)
                        )
                    ),
                    name: EnumMemberName::from('BAR'),
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
    public function threeValuelessMembers(): void
    {
        $enumDeclarationParser = new EnumDeclarationParser();
        $tokens = Tokenizer::fromSource(Source::fromString('enum Foo { BAR BAZ QUX }'))->getIterator();

        $expectedEnumDeclarationNode = new EnumDeclarationNode(
            location: new Location(
                sourcePath: Path::fromString(':memory:'),
                boundaries: Boundaries::fromPositions(
                    Position::create(0, 0, 0),
                    Position::create(23, 0, 23)
                )
            ),
            enumName: EnumName::from('Foo'),
            memberDeclarations: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    location: new Location(
                        sourcePath: Path::fromString(':memory:'),
                        boundaries: Boundaries::fromPositions(
                            Position::create(11, 0, 11),
                            Position::create(13, 0, 13)
                        )
                    ),
                    name: EnumMemberName::from('BAR'),
                    value: null
                ),
                new EnumMemberDeclarationNode(
                    location: new Location(
                        sourcePath: Path::fromString(':memory:'),
                        boundaries: Boundaries::fromPositions(
                            Position::create(15, 0, 15),
                            Position::create(17, 0, 17)
                        )
                    ),
                    name: EnumMemberName::from('BAZ'),
                    value: null
                ),
                new EnumMemberDeclarationNode(
                    location: new Location(
                        sourcePath: Path::fromString(':memory:'),
                        boundaries: Boundaries::fromPositions(
                            Position::create(19, 0, 19),
                            Position::create(21, 0, 21)
                        )
                    ),
                    name: EnumMemberName::from('QUX'),
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
    public function oneStringValueMember(): void
    {
        $enumDeclarationParser = new EnumDeclarationParser();
        $tokens = Tokenizer::fromSource(Source::fromString('enum Foo { BAR("BAR") }'))->getIterator();

        $expectedEnumDeclarationNode = new EnumDeclarationNode(
            location: new Location(
                sourcePath: Path::fromString(':memory:'),
                boundaries: Boundaries::fromPositions(
                    Position::create(0, 0, 0),
                    Position::create(22, 0, 22)
                )
            ),
            enumName: EnumName::from('Foo'),
            memberDeclarations: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    location: new Location(
                        sourcePath: Path::fromString(':memory:'),
                        boundaries: Boundaries::fromPositions(
                            Position::create(11, 0, 11),
                            Position::create(20, 0, 20)
                        )
                    ),
                    name: EnumMemberName::from('BAR'),
                    value: new StringLiteralNode(
                        location: new Location(
                            sourcePath: Path::fromString(':memory:'),
                            boundaries: Boundaries::fromPositions(
                                Position::create(16, 0, 16),
                                Position::create(18, 0, 18)
                            )
                        ),
                        value: 'BAR'
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
    public function sevenStringValueMembers(): void
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
        $tokens = Tokenizer::fromSource(Source::fromString($enumAsString))->getIterator();

        $expectedEnumDeclarationNode = new EnumDeclarationNode(
            location: new Location(
                sourcePath: Path::fromString(':memory:'),
                boundaries: Boundaries::fromPositions(
                    Position::create(0, 0, 0),
                    Position::create(149, 8, 0)
                )
            ),
            enumName: EnumName::from('Weekday'),
            memberDeclarations: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    location: new Location(
                        sourcePath: Path::fromString(':memory:'),
                        boundaries: Boundaries::fromPositions(
                            Position::create(19, 1, 4),
                            Position::create(31, 1, 16)
                        )
                    ),
                    name: EnumMemberName::from('MONDAY'),
                    value: new StringLiteralNode(
                        location: new Location(
                            sourcePath: Path::fromString(':memory:'),
                            boundaries: Boundaries::fromPositions(
                                Position::create(27, 1, 12),
                                Position::create(29, 1, 14)
                            )
                        ),
                        value: 'mon'
                    )
                ),
                new EnumMemberDeclarationNode(
                    location: new Location(
                        sourcePath: Path::fromString(':memory:'),
                        boundaries: Boundaries::fromPositions(
                            Position::create(37, 2, 4),
                            Position::create(50, 2, 17)
                        )
                    ),
                    name: EnumMemberName::from('TUESDAY'),
                    value: new StringLiteralNode(
                        location: new Location(
                            sourcePath: Path::fromString(':memory:'),
                            boundaries: Boundaries::fromPositions(
                                Position::create(46, 2, 13),
                                Position::create(48, 2, 15)
                            )
                        ),
                        value: 'tue'
                    )
                ),
                new EnumMemberDeclarationNode(
                    location: new Location(
                        sourcePath: Path::fromString(':memory:'),
                        boundaries: Boundaries::fromPositions(
                            Position::create(56, 3, 4),
                            Position::create(71, 3, 19)
                        )
                    ),
                    name: EnumMemberName::from('WEDNESDAY'),
                    value: new StringLiteralNode(
                        location: new Location(
                            sourcePath: Path::fromString(':memory:'),
                            boundaries: Boundaries::fromPositions(
                                Position::create(67, 3, 15),
                                Position::create(69, 3, 17)
                            )
                        ),
                        value: 'wed'
                    )
                ),
                new EnumMemberDeclarationNode(
                    location: new Location(
                        sourcePath: Path::fromString(':memory:'),
                        boundaries: Boundaries::fromPositions(
                            Position::create(77, 4, 4),
                            Position::create(91, 4, 18)
                        )
                    ),
                    name: EnumMemberName::from('THURSDAY'),
                    value: new StringLiteralNode(
                        location: new Location(
                            sourcePath: Path::fromString(':memory:'),
                            boundaries: Boundaries::fromPositions(
                                Position::create(87, 4, 14),
                                Position::create(89, 4, 16)
                            )
                        ),
                        value: 'thu'
                    )
                ),
                new EnumMemberDeclarationNode(
                    location: new Location(
                        sourcePath: Path::fromString(':memory:'),
                        boundaries: Boundaries::fromPositions(
                            Position::create(97, 5, 4),
                            Position::create(109, 5, 16)
                        )
                    ),
                    name: EnumMemberName::from('FRIDAY'),
                    value: new StringLiteralNode(
                        location: new Location(
                            sourcePath: Path::fromString(':memory:'),
                            boundaries: Boundaries::fromPositions(
                                Position::create(105, 5, 12),
                                Position::create(107, 5, 14)
                            )
                        ),
                        value: 'fri'
                    )
                ),
                new EnumMemberDeclarationNode(
                    location: new Location(
                        sourcePath: Path::fromString(':memory:'),
                        boundaries: Boundaries::fromPositions(
                            Position::create(115, 6, 4),
                            Position::create(129, 6, 18)
                        )
                    ),
                    name: EnumMemberName::from('SATURDAY'),
                    value: new StringLiteralNode(
                        location: new Location(
                            sourcePath: Path::fromString(':memory:'),
                            boundaries: Boundaries::fromPositions(
                                Position::create(125, 6, 14),
                                Position::create(127, 6, 16)
                            )
                        ),
                        value: 'sat'
                    )
                ),
                new EnumMemberDeclarationNode(
                    location: new Location(
                        sourcePath: Path::fromString(':memory:'),
                        boundaries: Boundaries::fromPositions(
                            Position::create(135, 7, 4),
                            Position::create(147, 7, 16)
                        )
                    ),
                    name: EnumMemberName::from('SUNDAY'),
                    value: new StringLiteralNode(
                        location: new Location(
                            sourcePath: Path::fromString(':memory:'),
                            boundaries: Boundaries::fromPositions(
                                Position::create(143, 7, 12),
                                Position::create(145, 7, 14)
                            )
                        ),
                        value: 'sun'
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
    public function oneIntegerValueMember(): void
    {
        $enumDeclarationParser = new EnumDeclarationParser();
        $tokens = Tokenizer::fromSource(Source::fromString('enum Foo { BAR(42) }'))->getIterator();

        $expectedEnumDeclarationNode = new EnumDeclarationNode(
            location: new Location(
                sourcePath: Path::fromString(':memory:'),
                boundaries: Boundaries::fromPositions(
                    Position::create(0, 0, 0),
                    Position::create(19, 0, 19)
                )
            ),
            enumName: EnumName::from('Foo'),
            memberDeclarations: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    location: new Location(
                        sourcePath: Path::fromString(':memory:'),
                        boundaries: Boundaries::fromPositions(
                            Position::create(11, 0, 11),
                            Position::create(17, 0, 17)
                        )
                    ),
                    name: EnumMemberName::from('BAR'),
                    value: new IntegerLiteralNode(
                        location: new Location(
                            sourcePath: Path::fromString(':memory:'),
                            boundaries: Boundaries::fromPositions(
                                Position::create(15, 0, 15),
                                Position::create(16, 0, 16)
                            )
                        ),
                        format: IntegerFormat::DECIMAL,
                        value: '42'
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
    public function twelveIntegerValueMembers(): void
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
        $tokens = Tokenizer::fromSource(Source::fromString($enumAsString))->getIterator();

        $expectedEnumDeclarationNode = new EnumDeclarationNode(
            location: new Location(
                sourcePath: Path::fromString(':memory:'),
                boundaries: Boundaries::fromPositions(
                    Position::create(0, 0, 0),
                    Position::create(186, 13, 0)
                )
            ),
            enumName: EnumName::from('Month'),
            memberDeclarations: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    location: new Location(
                        sourcePath: Path::fromString(':memory:'),
                        boundaries: Boundaries::fromPositions(
                            Position::create(17, 1, 4),
                            Position::create(26, 1, 13)
                        )
                    ),
                    name: EnumMemberName::from('JANUARY'),
                    value: new IntegerLiteralNode(
                        location: new Location(
                            sourcePath: Path::fromString(':memory:'),
                            boundaries: Boundaries::fromPositions(
                                Position::create(25, 1, 12),
                                Position::create(25, 1, 12)
                            )
                        ),
                        format: IntegerFormat::DECIMAL,
                        value: '1'
                    )
                ),
                new EnumMemberDeclarationNode(
                    location: new Location(
                        sourcePath: Path::fromString(':memory:'),
                        boundaries: Boundaries::fromPositions(
                            Position::create(32, 2, 4),
                            Position::create(42, 2, 14)
                        )
                    ),
                    name: EnumMemberName::from('FEBRUARY'),
                    value: new IntegerLiteralNode(
                        location: new Location(
                            sourcePath: Path::fromString(':memory:'),
                            boundaries: Boundaries::fromPositions(
                                Position::create(41, 2, 13),
                                Position::create(41, 2, 13)
                            )
                        ),
                        format: IntegerFormat::DECIMAL,
                        value: '2'
                    )
                ),
                new EnumMemberDeclarationNode(
                    location: new Location(
                        sourcePath: Path::fromString(':memory:'),
                        boundaries: Boundaries::fromPositions(
                            Position::create(48, 3, 4),
                            Position::create(55, 3, 11)
                        )
                    ),
                    name: EnumMemberName::from('MARCH'),
                    value: new IntegerLiteralNode(
                        location: new Location(
                            sourcePath: Path::fromString(':memory:'),
                            boundaries: Boundaries::fromPositions(
                                Position::create(54, 3, 10),
                                Position::create(54, 3, 10)
                            )
                        ),
                        format: IntegerFormat::DECIMAL,
                        value: '3'
                    )
                ),
                new EnumMemberDeclarationNode(
                    location: new Location(
                        sourcePath: Path::fromString(':memory:'),
                        boundaries: Boundaries::fromPositions(
                            Position::create(61, 4, 4),
                            Position::create(68, 4, 11)
                        )
                    ),
                    name: EnumMemberName::from('APRIL'),
                    value: new IntegerLiteralNode(
                        location: new Location(
                            sourcePath: Path::fromString(':memory:'),
                            boundaries: Boundaries::fromPositions(
                                Position::create(67, 4, 10),
                                Position::create(67, 4, 10)
                            )
                        ),
                        format: IntegerFormat::DECIMAL,
                        value: '4'
                    )
                ),
                new EnumMemberDeclarationNode(
                    location: new Location(
                        sourcePath: Path::fromString(':memory:'),
                        boundaries: Boundaries::fromPositions(
                            Position::create(74, 5, 4),
                            Position::create(79, 5, 9)
                        )
                    ),
                    name: EnumMemberName::from('MAY'),
                    value: new IntegerLiteralNode(
                        location: new Location(
                            sourcePath: Path::fromString(':memory:'),
                            boundaries: Boundaries::fromPositions(
                                Position::create(78, 5, 8),
                                Position::create(78, 5, 8)
                            )
                        ),
                        format: IntegerFormat::DECIMAL,
                        value: '5'
                    )
                ),
                new EnumMemberDeclarationNode(
                    location: new Location(
                        sourcePath: Path::fromString(':memory:'),
                        boundaries: Boundaries::fromPositions(
                            Position::create(85, 6, 4),
                            Position::create(91, 6, 10)
                        )
                    ),
                    name: EnumMemberName::from('JUNE'),
                    value: new IntegerLiteralNode(
                        location: new Location(
                            sourcePath: Path::fromString(':memory:'),
                            boundaries: Boundaries::fromPositions(
                                Position::create(90, 6, 9),
                                Position::create(90, 6, 9)
                            )
                        ),
                        format: IntegerFormat::DECIMAL,
                        value: '6'
                    )
                ),
                new EnumMemberDeclarationNode(
                    location: new Location(
                        sourcePath: Path::fromString(':memory:'),
                        boundaries: Boundaries::fromPositions(
                            Position::create(97, 7, 4),
                            Position::create(103, 7, 10)
                        )
                    ),
                    name: EnumMemberName::from('JULY'),
                    value: new IntegerLiteralNode(
                        location: new Location(
                            sourcePath: Path::fromString(':memory:'),
                            boundaries: Boundaries::fromPositions(
                                Position::create(102, 7, 9),
                                Position::create(102, 7, 9)
                            )
                        ),
                        format: IntegerFormat::DECIMAL,
                        value: '7'
                    )
                ),
                new EnumMemberDeclarationNode(
                    location: new Location(
                        sourcePath: Path::fromString(':memory:'),
                        boundaries: Boundaries::fromPositions(
                            Position::create(109, 8, 4),
                            Position::create(117, 8, 12)
                        )
                    ),
                    name: EnumMemberName::from('AUGUST'),
                    value: new IntegerLiteralNode(
                        location: new Location(
                            sourcePath: Path::fromString(':memory:'),
                            boundaries: Boundaries::fromPositions(
                                Position::create(116, 8, 11),
                                Position::create(116, 8, 11)
                            )
                        ),
                        format: IntegerFormat::DECIMAL,
                        value: '8'
                    )
                ),
                new EnumMemberDeclarationNode(
                    location: new Location(
                        sourcePath: Path::fromString(':memory:'),
                        boundaries: Boundaries::fromPositions(
                            Position::create(123, 9, 4),
                            Position::create(134, 9, 15)
                        )
                    ),
                    name: EnumMemberName::from('SEPTEMBER'),
                    value: new IntegerLiteralNode(
                        location: new Location(
                            sourcePath: Path::fromString(':memory:'),
                            boundaries: Boundaries::fromPositions(
                                Position::create(133, 9, 14),
                                Position::create(133, 9, 14)
                            )
                        ),
                        format: IntegerFormat::DECIMAL,
                        value: '9'
                    )
                ),
                new EnumMemberDeclarationNode(
                    location: new Location(
                        sourcePath: Path::fromString(':memory:'),
                        boundaries: Boundaries::fromPositions(
                            Position::create(140, 10, 4),
                            Position::create(150, 10, 14)
                        )
                    ),
                    name: EnumMemberName::from('OCTOBER'),
                    value: new IntegerLiteralNode(
                        location: new Location(
                            sourcePath: Path::fromString(':memory:'),
                            boundaries: Boundaries::fromPositions(
                                Position::create(148, 10, 12),
                                Position::create(149, 10, 13)
                            )
                        ),
                        format: IntegerFormat::DECIMAL,
                        value: '10'
                    )
                ),
                new EnumMemberDeclarationNode(
                    location: new Location(
                        sourcePath: Path::fromString(':memory:'),
                        boundaries: Boundaries::fromPositions(
                            Position::create(156, 11, 4),
                            Position::create(167, 11, 15)
                        )
                    ),
                    name: EnumMemberName::from('NOVEMBER'),
                    value: new IntegerLiteralNode(
                        location: new Location(
                            sourcePath: Path::fromString(':memory:'),
                            boundaries: Boundaries::fromPositions(
                                Position::create(165, 11, 13),
                                Position::create(166, 11, 14)
                            )
                        ),
                        format: IntegerFormat::DECIMAL,
                        value: '11'
                    )
                ),
                new EnumMemberDeclarationNode(
                    location: new Location(
                        sourcePath: Path::fromString(':memory:'),
                        boundaries: Boundaries::fromPositions(
                            Position::create(173, 12, 4),
                            Position::create(184, 12, 15)
                        )
                    ),
                    name: EnumMemberName::from('DECEMBER'),
                    value: new IntegerLiteralNode(
                        location: new Location(
                            sourcePath: Path::fromString(':memory:'),
                            boundaries: Boundaries::fromPositions(
                                Position::create(182, 12, 13),
                                Position::create(183, 12, 14)
                            )
                        ),
                        format: IntegerFormat::DECIMAL,
                        value: '12'
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
