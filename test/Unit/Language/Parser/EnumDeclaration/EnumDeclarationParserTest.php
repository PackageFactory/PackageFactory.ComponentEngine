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

use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumMemberDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumMemberDeclarationNodes;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumMemberName;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumName;
use PackageFactory\ComponentEngine\Language\AST\Node\IntegerLiteral\IntegerFormat;
use PackageFactory\ComponentEngine\Language\AST\Node\IntegerLiteral\IntegerLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StringLiteral\StringLiteralNode;
use PackageFactory\ComponentEngine\Language\Parser\EnumDeclaration\EnumDeclarationParser;
use PackageFactory\ComponentEngine\Language\AST\NodeAttributes\NodeAttributes;
use PackageFactory\ComponentEngine\Parser\Source\Range;
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
            attributes: new NodeAttributes(
                pathToSource: Path::fromString(':memory:'),
                rangeInSource: Range::from(
                    Position::from(0, 0, 0),
                    Position::from(15, 0, 15)
                )
            ),
            enumName: EnumName::from('Foo'),
            memberDeclarations: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(11, 0, 11),
                            Position::from(13, 0, 13)
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
            attributes: new NodeAttributes(
                pathToSource: Path::fromString(':memory:'),
                rangeInSource: Range::from(
                    Position::from(0, 0, 0),
                    Position::from(23, 0, 23)
                )
            ),
            enumName: EnumName::from('Foo'),
            memberDeclarations: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(11, 0, 11),
                            Position::from(13, 0, 13)
                        )
                    ),
                    name: EnumMemberName::from('BAR'),
                    value: null
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(15, 0, 15),
                            Position::from(17, 0, 17)
                        )
                    ),
                    name: EnumMemberName::from('BAZ'),
                    value: null
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(19, 0, 19),
                            Position::from(21, 0, 21)
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
            attributes: new NodeAttributes(
                pathToSource: Path::fromString(':memory:'),
                rangeInSource: Range::from(
                    Position::from(0, 0, 0),
                    Position::from(22, 0, 22)
                )
            ),
            enumName: EnumName::from('Foo'),
            memberDeclarations: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(11, 0, 11),
                            Position::from(20, 0, 20)
                        )
                    ),
                    name: EnumMemberName::from('BAR'),
                    value: new StringLiteralNode(
                        attributes: new NodeAttributes(
                            pathToSource: Path::fromString(':memory:'),
                            rangeInSource: Range::from(
                                Position::from(16, 0, 16),
                                Position::from(18, 0, 18)
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
            attributes: new NodeAttributes(
                pathToSource: Path::fromString(':memory:'),
                rangeInSource: Range::from(
                    Position::from(0, 0, 0),
                    Position::from(149, 8, 0)
                )
            ),
            enumName: EnumName::from('Weekday'),
            memberDeclarations: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(19, 1, 4),
                            Position::from(31, 1, 16)
                        )
                    ),
                    name: EnumMemberName::from('MONDAY'),
                    value: new StringLiteralNode(
                        attributes: new NodeAttributes(
                            pathToSource: Path::fromString(':memory:'),
                            rangeInSource: Range::from(
                                Position::from(27, 1, 12),
                                Position::from(29, 1, 14)
                            )
                        ),
                        value: 'mon'
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(37, 2, 4),
                            Position::from(50, 2, 17)
                        )
                    ),
                    name: EnumMemberName::from('TUESDAY'),
                    value: new StringLiteralNode(
                        attributes: new NodeAttributes(
                            pathToSource: Path::fromString(':memory:'),
                            rangeInSource: Range::from(
                                Position::from(46, 2, 13),
                                Position::from(48, 2, 15)
                            )
                        ),
                        value: 'tue'
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(56, 3, 4),
                            Position::from(71, 3, 19)
                        )
                    ),
                    name: EnumMemberName::from('WEDNESDAY'),
                    value: new StringLiteralNode(
                        attributes: new NodeAttributes(
                            pathToSource: Path::fromString(':memory:'),
                            rangeInSource: Range::from(
                                Position::from(67, 3, 15),
                                Position::from(69, 3, 17)
                            )
                        ),
                        value: 'wed'
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(77, 4, 4),
                            Position::from(91, 4, 18)
                        )
                    ),
                    name: EnumMemberName::from('THURSDAY'),
                    value: new StringLiteralNode(
                        attributes: new NodeAttributes(
                            pathToSource: Path::fromString(':memory:'),
                            rangeInSource: Range::from(
                                Position::from(87, 4, 14),
                                Position::from(89, 4, 16)
                            )
                        ),
                        value: 'thu'
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(97, 5, 4),
                            Position::from(109, 5, 16)
                        )
                    ),
                    name: EnumMemberName::from('FRIDAY'),
                    value: new StringLiteralNode(
                        attributes: new NodeAttributes(
                            pathToSource: Path::fromString(':memory:'),
                            rangeInSource: Range::from(
                                Position::from(105, 5, 12),
                                Position::from(107, 5, 14)
                            )
                        ),
                        value: 'fri'
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(115, 6, 4),
                            Position::from(129, 6, 18)
                        )
                    ),
                    name: EnumMemberName::from('SATURDAY'),
                    value: new StringLiteralNode(
                        attributes: new NodeAttributes(
                            pathToSource: Path::fromString(':memory:'),
                            rangeInSource: Range::from(
                                Position::from(125, 6, 14),
                                Position::from(127, 6, 16)
                            )
                        ),
                        value: 'sat'
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(135, 7, 4),
                            Position::from(147, 7, 16)
                        )
                    ),
                    name: EnumMemberName::from('SUNDAY'),
                    value: new StringLiteralNode(
                        attributes: new NodeAttributes(
                            pathToSource: Path::fromString(':memory:'),
                            rangeInSource: Range::from(
                                Position::from(143, 7, 12),
                                Position::from(145, 7, 14)
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
    public function oneBinaryIntegerValueMember(): void
    {
        $enumDeclarationParser = new EnumDeclarationParser();
        $tokens = Tokenizer::fromSource(Source::fromString('enum Foo { BAR(0b101) }'))->getIterator();

        $expectedEnumDeclarationNode = new EnumDeclarationNode(
            attributes: new NodeAttributes(
                pathToSource: Path::fromString(':memory:'),
                rangeInSource: Range::from(
                    Position::from(0, 0, 0),
                    Position::from(22, 0, 22)
                )
            ),
            enumName: EnumName::from('Foo'),
            memberDeclarations: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(11, 0, 11),
                            Position::from(20, 0, 20)
                        )
                    ),
                    name: EnumMemberName::from('BAR'),
                    value: new IntegerLiteralNode(
                        attributes: new NodeAttributes(
                            pathToSource: Path::fromString(':memory:'),
                            rangeInSource: Range::from(
                                Position::from(15, 0, 15),
                                Position::from(19, 0, 19)
                            )
                        ),
                        format: IntegerFormat::BINARY,
                        value: '0b101'
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
    public function oneOctalIntegerValueMember(): void
    {
        $enumDeclarationParser = new EnumDeclarationParser();
        $tokens = Tokenizer::fromSource(Source::fromString('enum Foo { BAR(0o644) }'))->getIterator();

        $expectedEnumDeclarationNode = new EnumDeclarationNode(
            attributes: new NodeAttributes(
                pathToSource: Path::fromString(':memory:'),
                rangeInSource: Range::from(
                    Position::from(0, 0, 0),
                    Position::from(22, 0, 22)
                )
            ),
            enumName: EnumName::from('Foo'),
            memberDeclarations: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(11, 0, 11),
                            Position::from(20, 0, 20)
                        )
                    ),
                    name: EnumMemberName::from('BAR'),
                    value: new IntegerLiteralNode(
                        attributes: new NodeAttributes(
                            pathToSource: Path::fromString(':memory:'),
                            rangeInSource: Range::from(
                                Position::from(15, 0, 15),
                                Position::from(19, 0, 19)
                            )
                        ),
                        format: IntegerFormat::OCTAL,
                        value: '0o644'
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
    public function oneDecimalIntegerValueMember(): void
    {
        $enumDeclarationParser = new EnumDeclarationParser();
        $tokens = Tokenizer::fromSource(Source::fromString('enum Foo { BAR(42) }'))->getIterator();

        $expectedEnumDeclarationNode = new EnumDeclarationNode(
            attributes: new NodeAttributes(
                pathToSource: Path::fromString(':memory:'),
                rangeInSource: Range::from(
                    Position::from(0, 0, 0),
                    Position::from(19, 0, 19)
                )
            ),
            enumName: EnumName::from('Foo'),
            memberDeclarations: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(11, 0, 11),
                            Position::from(17, 0, 17)
                        )
                    ),
                    name: EnumMemberName::from('BAR'),
                    value: new IntegerLiteralNode(
                        attributes: new NodeAttributes(
                            pathToSource: Path::fromString(':memory:'),
                            rangeInSource: Range::from(
                                Position::from(15, 0, 15),
                                Position::from(16, 0, 16)
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
    public function oneHexadecimalIntegerValueMember(): void
    {
        $enumDeclarationParser = new EnumDeclarationParser();
        $tokens = Tokenizer::fromSource(Source::fromString('enum Foo { BAR(0xABC) }'))->getIterator();

        $expectedEnumDeclarationNode = new EnumDeclarationNode(
            attributes: new NodeAttributes(
                pathToSource: Path::fromString(':memory:'),
                rangeInSource: Range::from(
                    Position::from(0, 0, 0),
                    Position::from(22, 0, 22)
                )
            ),
            enumName: EnumName::from('Foo'),
            memberDeclarations: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(11, 0, 11),
                            Position::from(20, 0, 20)
                        )
                    ),
                    name: EnumMemberName::from('BAR'),
                    value: new IntegerLiteralNode(
                        attributes: new NodeAttributes(
                            pathToSource: Path::fromString(':memory:'),
                            rangeInSource: Range::from(
                                Position::from(15, 0, 15),
                                Position::from(19, 0, 19)
                            )
                        ),
                        format: IntegerFormat::HEXADECIMAL,
                        value: '0xABC'
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
            attributes: new NodeAttributes(
                pathToSource: Path::fromString(':memory:'),
                rangeInSource: Range::from(
                    Position::from(0, 0, 0),
                    Position::from(186, 13, 0)
                )
            ),
            enumName: EnumName::from('Month'),
            memberDeclarations: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(17, 1, 4),
                            Position::from(26, 1, 13)
                        )
                    ),
                    name: EnumMemberName::from('JANUARY'),
                    value: new IntegerLiteralNode(
                        attributes: new NodeAttributes(
                            pathToSource: Path::fromString(':memory:'),
                            rangeInSource: Range::from(
                                Position::from(25, 1, 12),
                                Position::from(25, 1, 12)
                            )
                        ),
                        format: IntegerFormat::DECIMAL,
                        value: '1'
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(32, 2, 4),
                            Position::from(42, 2, 14)
                        )
                    ),
                    name: EnumMemberName::from('FEBRUARY'),
                    value: new IntegerLiteralNode(
                        attributes: new NodeAttributes(
                            pathToSource: Path::fromString(':memory:'),
                            rangeInSource: Range::from(
                                Position::from(41, 2, 13),
                                Position::from(41, 2, 13)
                            )
                        ),
                        format: IntegerFormat::DECIMAL,
                        value: '2'
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(48, 3, 4),
                            Position::from(55, 3, 11)
                        )
                    ),
                    name: EnumMemberName::from('MARCH'),
                    value: new IntegerLiteralNode(
                        attributes: new NodeAttributes(
                            pathToSource: Path::fromString(':memory:'),
                            rangeInSource: Range::from(
                                Position::from(54, 3, 10),
                                Position::from(54, 3, 10)
                            )
                        ),
                        format: IntegerFormat::DECIMAL,
                        value: '3'
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(61, 4, 4),
                            Position::from(68, 4, 11)
                        )
                    ),
                    name: EnumMemberName::from('APRIL'),
                    value: new IntegerLiteralNode(
                        attributes: new NodeAttributes(
                            pathToSource: Path::fromString(':memory:'),
                            rangeInSource: Range::from(
                                Position::from(67, 4, 10),
                                Position::from(67, 4, 10)
                            )
                        ),
                        format: IntegerFormat::DECIMAL,
                        value: '4'
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(74, 5, 4),
                            Position::from(79, 5, 9)
                        )
                    ),
                    name: EnumMemberName::from('MAY'),
                    value: new IntegerLiteralNode(
                        attributes: new NodeAttributes(
                            pathToSource: Path::fromString(':memory:'),
                            rangeInSource: Range::from(
                                Position::from(78, 5, 8),
                                Position::from(78, 5, 8)
                            )
                        ),
                        format: IntegerFormat::DECIMAL,
                        value: '5'
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(85, 6, 4),
                            Position::from(91, 6, 10)
                        )
                    ),
                    name: EnumMemberName::from('JUNE'),
                    value: new IntegerLiteralNode(
                        attributes: new NodeAttributes(
                            pathToSource: Path::fromString(':memory:'),
                            rangeInSource: Range::from(
                                Position::from(90, 6, 9),
                                Position::from(90, 6, 9)
                            )
                        ),
                        format: IntegerFormat::DECIMAL,
                        value: '6'
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(97, 7, 4),
                            Position::from(103, 7, 10)
                        )
                    ),
                    name: EnumMemberName::from('JULY'),
                    value: new IntegerLiteralNode(
                        attributes: new NodeAttributes(
                            pathToSource: Path::fromString(':memory:'),
                            rangeInSource: Range::from(
                                Position::from(102, 7, 9),
                                Position::from(102, 7, 9)
                            )
                        ),
                        format: IntegerFormat::DECIMAL,
                        value: '7'
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(109, 8, 4),
                            Position::from(117, 8, 12)
                        )
                    ),
                    name: EnumMemberName::from('AUGUST'),
                    value: new IntegerLiteralNode(
                        attributes: new NodeAttributes(
                            pathToSource: Path::fromString(':memory:'),
                            rangeInSource: Range::from(
                                Position::from(116, 8, 11),
                                Position::from(116, 8, 11)
                            )
                        ),
                        format: IntegerFormat::DECIMAL,
                        value: '8'
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(123, 9, 4),
                            Position::from(134, 9, 15)
                        )
                    ),
                    name: EnumMemberName::from('SEPTEMBER'),
                    value: new IntegerLiteralNode(
                        attributes: new NodeAttributes(
                            pathToSource: Path::fromString(':memory:'),
                            rangeInSource: Range::from(
                                Position::from(133, 9, 14),
                                Position::from(133, 9, 14)
                            )
                        ),
                        format: IntegerFormat::DECIMAL,
                        value: '9'
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(140, 10, 4),
                            Position::from(150, 10, 14)
                        )
                    ),
                    name: EnumMemberName::from('OCTOBER'),
                    value: new IntegerLiteralNode(
                        attributes: new NodeAttributes(
                            pathToSource: Path::fromString(':memory:'),
                            rangeInSource: Range::from(
                                Position::from(148, 10, 12),
                                Position::from(149, 10, 13)
                            )
                        ),
                        format: IntegerFormat::DECIMAL,
                        value: '10'
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(156, 11, 4),
                            Position::from(167, 11, 15)
                        )
                    ),
                    name: EnumMemberName::from('NOVEMBER'),
                    value: new IntegerLiteralNode(
                        attributes: new NodeAttributes(
                            pathToSource: Path::fromString(':memory:'),
                            rangeInSource: Range::from(
                                Position::from(165, 11, 13),
                                Position::from(166, 11, 14)
                            )
                        ),
                        format: IntegerFormat::DECIMAL,
                        value: '11'
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        pathToSource: Path::fromString(':memory:'),
                        rangeInSource: Range::from(
                            Position::from(173, 12, 4),
                            Position::from(184, 12, 15)
                        )
                    ),
                    name: EnumMemberName::from('DECEMBER'),
                    value: new IntegerLiteralNode(
                        attributes: new NodeAttributes(
                            pathToSource: Path::fromString(':memory:'),
                            rangeInSource: Range::from(
                                Position::from(182, 12, 13),
                                Position::from(183, 12, 14)
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
