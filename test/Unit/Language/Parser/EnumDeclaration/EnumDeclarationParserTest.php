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
use PackageFactory\ComponentEngine\Language\AST\NodeAttributes\NodeAttributes;
use PackageFactory\ComponentEngine\Parser\Source\Range;
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
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 15)
                )
            ),
            name: new EnumNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 5),
                        new Position(0, 7)
                    )
                ),
                value: EnumName::from('Foo')
            ),
            members: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 11),
                            new Position(0, 13)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 11),
                                new Position(0, 13)
                            )
                        ),
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
    public function threeValuelessMembers(): void
    {
        $enumDeclarationParser = new EnumDeclarationParser();
        $tokens = Tokenizer::fromSource(Source::fromString('enum Foo { BAR BAZ QUX }'))->getIterator();

        $expectedEnumDeclarationNode = new EnumDeclarationNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 23)
                )
            ),
            name: new EnumNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 5),
                        new Position(0, 7)
                    )
                ),
                value: EnumName::from('Foo')
            ),
            members: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 11),
                            new Position(0, 13)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 11),
                                new Position(0, 13)
                            )
                        ),
                        value: EnumMemberName::from('BAR')
                    ),
                    value: null
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 15),
                            new Position(0, 17)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 15),
                                new Position(0, 17)
                            )
                        ),
                        value: EnumMemberName::from('BAZ')
                    ),
                    value: null
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 19),
                            new Position(0, 21)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 19),
                                new Position(0, 21)
                            )
                        ),
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
    public function oneStringValueMember(): void
    {
        $enumDeclarationParser = new EnumDeclarationParser();
        $tokens = Tokenizer::fromSource(Source::fromString('enum Foo { BAR("BAR") }'))->getIterator();

        $expectedEnumDeclarationNode = new EnumDeclarationNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 22)
                )
            ),
            name: new EnumNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 5),
                        new Position(0, 7)
                    )
                ),
                value: EnumName::from('Foo')
            ),
            members: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 11),
                            new Position(0, 20)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 11),
                                new Position(0, 13)
                            )
                        ),
                        value: EnumMemberName::from('BAR')
                    ),
                    value: new EnumMemberValueNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 14),
                                new Position(0, 20)
                            )
                        ),
                        value: new StringLiteralNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(0, 16),
                                    new Position(0, 18)
                                )
                            ),
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
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(8, 0)
                )
            ),
            name: new EnumNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 5),
                        new Position(0, 11)
                    )
                ),
                value: EnumName::from('Weekday')
            ),
            members: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(1, 4),
                            new Position(1, 16)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(1, 4),
                                new Position(1, 9)
                            )
                        ),
                        value: EnumMemberName::from('MONDAY')
                    ),
                    value: new EnumMemberValueNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(1, 10),
                                new Position(1, 16)
                            )
                        ),
                        value: new StringLiteralNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(1, 12),
                                    new Position(1, 14)
                                )
                            ),
                            value: 'mon'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(2, 4),
                            new Position(2, 17)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(2, 4),
                                new Position(2, 10)
                            )
                        ),
                        value: EnumMemberName::from('TUESDAY')
                    ),
                    value: new EnumMemberValueNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(2, 11),
                                new Position(2, 17)
                            )
                        ),
                        value: new StringLiteralNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(2, 13),
                                    new Position(2, 15)
                                )
                            ),
                            value: 'tue'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(3, 4),
                            new Position(3, 19)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(3, 4),
                                new Position(3, 12)
                            )
                        ),
                        value: EnumMemberName::from('WEDNESDAY')
                    ),
                    value: new EnumMemberValueNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(3, 13),
                                new Position(3, 19)
                            )
                        ),
                        value: new StringLiteralNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(3, 15),
                                    new Position(3, 17)
                                )
                            ),
                            value: 'wed'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(4, 4),
                            new Position(4, 18)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(4, 4),
                                new Position(4, 11)
                            )
                        ),
                        value: EnumMemberName::from('THURSDAY')
                    ),
                    value: new EnumMemberValueNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(4, 12),
                                new Position(4, 18)
                            )
                        ),
                        value: new StringLiteralNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(4, 14),
                                    new Position(4, 16)
                                )
                            ),
                            value: 'thu'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(5, 4),
                            new Position(5, 16)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(5, 4),
                                new Position(5, 9)
                            )
                        ),
                        value: EnumMemberName::from('FRIDAY')
                    ),
                    value: new EnumMemberValueNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(5, 10),
                                new Position(5, 16)
                            )
                        ),
                        value: new StringLiteralNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(5, 12),
                                    new Position(5, 14)
                                )
                            ),
                            value: 'fri'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(6, 4),
                            new Position(6, 18)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(6, 4),
                                new Position(6, 11)
                            )
                        ),
                        value: EnumMemberName::from('SATURDAY')
                    ),
                    value: new EnumMemberValueNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(6, 12),
                                new Position(6, 18)
                            )
                        ),
                        value: new StringLiteralNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(6, 14),
                                    new Position(6, 16)
                                )
                            ),
                            value: 'sat'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(7, 4),
                            new Position(7, 16)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(7, 4),
                                new Position(7, 9)
                            )
                        ),
                        value: EnumMemberName::from('SUNDAY')
                    ),
                    value: new EnumMemberValueNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(7, 10),
                                new Position(7, 16)
                            )
                        ),
                        value: new StringLiteralNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(7, 12),
                                    new Position(7, 14)
                                )
                            ),
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
    public function oneBinaryIntegerValueMember(): void
    {
        $enumDeclarationParser = new EnumDeclarationParser();
        $tokens = Tokenizer::fromSource(Source::fromString('enum Foo { BAR(0b101) }'))->getIterator();

        $expectedEnumDeclarationNode = new EnumDeclarationNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 22)
                )
            ),
            name: new EnumNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 5),
                        new Position(0, 7)
                    )
                ),
                value: EnumName::from('Foo')
            ),
            members: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 11),
                            new Position(0, 20)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 11),
                                new Position(0, 13)
                            )
                        ),
                        value: EnumMemberName::from('BAR')
                    ),
                    value: new EnumMemberValueNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 14),
                                new Position(0, 20)
                            )
                        ),
                        value: new IntegerLiteralNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(0, 15),
                                    new Position(0, 19)
                                )
                            ),
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
    public function oneOctalIntegerValueMember(): void
    {
        $enumDeclarationParser = new EnumDeclarationParser();
        $tokens = Tokenizer::fromSource(Source::fromString('enum Foo { BAR(0o644) }'))->getIterator();

        $expectedEnumDeclarationNode = new EnumDeclarationNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 22)
                )
            ),
            name: new EnumNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 5),
                        new Position(0, 7)
                    )
                ),
                value: EnumName::from('Foo')
            ),
            members: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 11),
                            new Position(0, 20)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 11),
                                new Position(0, 13)
                            )
                        ),
                        value: EnumMemberName::from('BAR')
                    ),
                    value: new EnumMemberValueNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 14),
                                new Position(0, 20)
                            )
                        ),
                        value: new IntegerLiteralNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(0, 15),
                                    new Position(0, 19)
                                )
                            ),
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
    public function oneDecimalIntegerValueMember(): void
    {
        $enumDeclarationParser = new EnumDeclarationParser();
        $tokens = Tokenizer::fromSource(Source::fromString('enum Foo { BAR(42) }'))->getIterator();

        $expectedEnumDeclarationNode = new EnumDeclarationNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 19)
                )
            ),
            name: new EnumNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 5),
                        new Position(0, 7)
                    )
                ),
                value: EnumName::from('Foo')
            ),
            members: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 11),
                            new Position(0, 17)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 11),
                                new Position(0, 13)
                            )
                        ),
                        value: EnumMemberName::from('BAR')
                    ),
                    value: new EnumMemberValueNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 14),
                                new Position(0, 17)
                            )
                        ),
                        value: new IntegerLiteralNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(0, 15),
                                    new Position(0, 16)
                                )
                            ),
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
    public function oneHexadecimalIntegerValueMember(): void
    {
        $enumDeclarationParser = new EnumDeclarationParser();
        $tokens = Tokenizer::fromSource(Source::fromString('enum Foo { BAR(0xABC) }'))->getIterator();

        $expectedEnumDeclarationNode = new EnumDeclarationNode(
            attributes: new NodeAttributes(
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(0, 22)
                )
            ),
            name: new EnumNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 5),
                        new Position(0, 7)
                    )
                ),
                value: EnumName::from('Foo')
            ),
            members: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(0, 11),
                            new Position(0, 20)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 11),
                                new Position(0, 13)
                            )
                        ),
                        value: EnumMemberName::from('BAR')
                    ),
                    value: new EnumMemberValueNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(0, 14),
                                new Position(0, 20)
                            )
                        ),
                        value: new IntegerLiteralNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(0, 15),
                                    new Position(0, 19)
                                )
                            ),
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
                rangeInSource: Range::from(
                    new Position(0, 0),
                    new Position(13, 0)
                )
            ),
            name: new EnumNameNode(
                attributes: new NodeAttributes(
                    rangeInSource: Range::from(
                        new Position(0, 5),
                        new Position(0, 9)
                    )
                ),
                value: EnumName::from('Month')
            ),
            members: new EnumMemberDeclarationNodes(
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(1, 4),
                            new Position(1, 13)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(1, 4),
                                new Position(1, 10)
                            )
                        ),
                        value: EnumMemberName::from('JANUARY')
                    ),
                    value: new EnumMemberValueNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(1, 11),
                                new Position(1, 13)
                            )
                        ),
                        value: new IntegerLiteralNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(1, 12),
                                    new Position(1, 12)
                                )
                            ),
                            format: IntegerFormat::DECIMAL,
                            value: '1'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(2, 4),
                            new Position(2, 14)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(2, 4),
                                new Position(2, 11)
                            )
                        ),
                        value: EnumMemberName::from('FEBRUARY')
                    ),
                    value: new EnumMemberValueNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(2, 12),
                                new Position(2, 14)
                            )
                        ),
                        value: new IntegerLiteralNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(2, 13),
                                    new Position(2, 13)
                                )
                            ),
                            format: IntegerFormat::DECIMAL,
                            value: '2'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(3, 4),
                            new Position(3, 11)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(3, 4),
                                new Position(3, 8)
                            )
                        ),
                        value: EnumMemberName::from('MARCH')
                    ),
                    value: new EnumMemberValueNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(3, 9),
                                new Position(3, 11)
                            )
                        ),
                        value: new IntegerLiteralNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(3, 10),
                                    new Position(3, 10)
                                )
                            ),
                            format: IntegerFormat::DECIMAL,
                            value: '3'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(4, 4),
                            new Position(4, 11)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(4, 4),
                                new Position(4, 8)
                            )
                        ),
                        value: EnumMemberName::from('APRIL')
                    ),
                    value: new EnumMemberValueNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(4, 9),
                                new Position(4, 11)
                            )
                        ),
                        value: new IntegerLiteralNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(4, 10),
                                    new Position(4, 10)
                                )
                            ),
                            format: IntegerFormat::DECIMAL,
                            value: '4'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(5, 4),
                            new Position(5, 9)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(5, 4),
                                new Position(5, 6)
                            )
                        ),
                        value: EnumMemberName::from('MAY')
                    ),
                    value: new EnumMemberValueNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(5, 7),
                                new Position(5, 9)
                            )
                        ),
                        value: new IntegerLiteralNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(5, 8),
                                    new Position(5, 8)
                                )
                            ),
                            format: IntegerFormat::DECIMAL,
                            value: '5'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(6, 4),
                            new Position(6, 10)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(6, 4),
                                new Position(6, 7)
                            )
                        ),
                        value: EnumMemberName::from('JUNE')
                    ),
                    value: new EnumMemberValueNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(6, 8),
                                new Position(6, 10)
                            )
                        ),
                        value: new IntegerLiteralNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(6, 9),
                                    new Position(6, 9)
                                )
                            ),
                            format: IntegerFormat::DECIMAL,
                            value: '6'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(7, 4),
                            new Position(7, 10)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(7, 4),
                                new Position(7, 7)
                            )
                        ),
                        value: EnumMemberName::from('JULY')
                    ),
                    value: new EnumMemberValueNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(7, 8),
                                new Position(7, 10)
                            )
                        ),
                        value: new IntegerLiteralNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(7, 9),
                                    new Position(7, 9)
                                )
                            ),
                            format: IntegerFormat::DECIMAL,
                            value: '7'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(8, 4),
                            new Position(8, 12)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(8, 4),
                                new Position(8, 9)
                            )
                        ),
                        value: EnumMemberName::from('AUGUST')
                    ),
                    value: new EnumMemberValueNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(8, 10),
                                new Position(8, 12)
                            )
                        ),
                        value: new IntegerLiteralNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(8, 11),
                                    new Position(8, 11)
                                )
                            ),
                            format: IntegerFormat::DECIMAL,
                            value: '8'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(9, 4),
                            new Position(9, 15)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(9, 4),
                                new Position(9, 12)
                            )
                        ),
                        value: EnumMemberName::from('SEPTEMBER')
                    ),
                    value: new EnumMemberValueNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(9, 13),
                                new Position(9, 15)
                            )
                        ),
                        value: new IntegerLiteralNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(9, 14),
                                    new Position(9, 14)
                                )
                            ),
                            format: IntegerFormat::DECIMAL,
                            value: '9'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(10, 4),
                            new Position(10, 14)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(10, 4),
                                new Position(10, 10)
                            )
                        ),
                        value: EnumMemberName::from('OCTOBER')
                    ),
                    value: new EnumMemberValueNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(10, 11),
                                new Position(10, 14)
                            )
                        ),
                        value: new IntegerLiteralNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(10, 12),
                                    new Position(10, 13)
                                )
                            ),
                            format: IntegerFormat::DECIMAL,
                            value: '10'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(11, 4),
                            new Position(11, 15)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(11, 4),
                                new Position(11, 11)
                            )
                        ),
                        value: EnumMemberName::from('NOVEMBER')
                    ),
                    value: new EnumMemberValueNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(11, 12),
                                new Position(11, 15)
                            )
                        ),
                        value: new IntegerLiteralNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(11, 13),
                                    new Position(11, 14)
                                )
                            ),
                            format: IntegerFormat::DECIMAL,
                            value: '11'
                        )
                    )
                ),
                new EnumMemberDeclarationNode(
                    attributes: new NodeAttributes(
                        rangeInSource: Range::from(
                            new Position(12, 4),
                            new Position(12, 15)
                        )
                    ),
                    name: new EnumMemberNameNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(12, 4),
                                new Position(12, 11)
                            )
                        ),
                        value: EnumMemberName::from('DECEMBER')
                    ),
                    value: new EnumMemberValueNode(
                        attributes: new NodeAttributes(
                            rangeInSource: Range::from(
                                new Position(12, 12),
                                new Position(12, 15)
                            )
                        ),
                        value: new IntegerLiteralNode(
                            attributes: new NodeAttributes(
                                rangeInSource: Range::from(
                                    new Position(12, 13),
                                    new Position(12, 14)
                                )
                            ),
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
