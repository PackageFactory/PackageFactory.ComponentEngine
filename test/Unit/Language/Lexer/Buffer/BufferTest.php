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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Lexer\Buffer;

use PackageFactory\ComponentEngine\Language\Lexer\Buffer\Buffer;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PHPUnit\Framework\TestCase;

final class BufferTest extends TestCase
{
    /**
     * @test
     */
    public function testInitialBufferState(): void
    {
        $this->assertBufferState(
            expectedStart: Position::zero(),
            expectedEnd: Position::zero(),
            expectedContents: '',
            actualBuffer: new Buffer()
        );
    }

    /**
     * @test
     */
    public function appendCapturesTheGivenCharacterAndIncrementsTheColumnNumberOfTheEndPosition(): void
    {
        $buffer = new Buffer();
        $buffer->append('A');

        $this->assertBufferState(
            expectedStart: Position::zero(),
            expectedEnd: Position::zero(),
            expectedContents: 'A',
            actualBuffer: $buffer
        );

        $buffer->append('B');

        $this->assertBufferState(
            expectedStart: Position::zero(),
            expectedEnd: Position::from(0, 1),
            expectedContents: 'AB',
            actualBuffer: $buffer
        );

        $buffer->append('C');

        $this->assertBufferState(
            expectedStart: Position::zero(),
            expectedEnd: Position::from(0, 2),
            expectedContents: 'ABC',
            actualBuffer: $buffer
        );
    }

    /**
     * @test
     */
    public function appendAcceptsMultiByteCharactersAndCountsThemAsOneCharacterEach(): void
    {
        $buffer = new Buffer();
        $buffer->append('🌵');

        $this->assertBufferState(
            expectedStart: Position::zero(),
            expectedEnd: Position::zero(),
            expectedContents: '🌵',
            actualBuffer: $buffer
        );

        $buffer->append('🆚');

        $this->assertBufferState(
            expectedStart: Position::zero(),
            expectedEnd: Position::from(0, 1),
            expectedContents: '🌵🆚',
            actualBuffer: $buffer
        );

        $buffer->append('⌚️');

        $this->assertBufferState(
            expectedStart: Position::zero(),
            expectedEnd: Position::from(0, 2),
            expectedContents: '🌵🆚⌚️',
            actualBuffer: $buffer
        );
    }

    /**
     * @test
     */
    public function appendCapturesNewLineCharacterIncrementingTheLineNumberOfTheEndPosition(): void
    {
        $buffer = new Buffer();
        $buffer->append('A');

        $this->assertBufferState(
            expectedStart: Position::zero(),
            expectedEnd: Position::zero(),
            expectedContents: 'A',
            actualBuffer: $buffer
        );

        $buffer->append("\n");

        $this->assertBufferState(
            expectedStart: Position::zero(),
            expectedEnd: Position::from(0, 1),
            expectedContents: "A\n",
            actualBuffer: $buffer
        );

        $buffer->append('B');

        $this->assertBufferState(
            expectedStart: Position::zero(),
            expectedEnd: Position::from(1, 0),
            expectedContents: "A\nB",
            actualBuffer: $buffer
        );
    }

    /**
     * @test
     */
    public function flushEmptiesTheContentsAndSetsNewBoundingPositions(): void
    {
        $buffer = new Buffer();
        $buffer->append('A');
        $buffer->append('B');
        $buffer->append('C');

        $buffer->flush();

        $this->assertBufferState(
            expectedStart: Position::from(0, 3),
            expectedEnd: Position::from(0, 3),
            expectedContents: '',
            actualBuffer: $buffer
        );

        $buffer = new Buffer();
        $buffer->append('A');
        $buffer->append("\n");
        $buffer->append('C');

        $buffer->flush();

        $this->assertBufferState(
            expectedStart: Position::from(1, 1),
            expectedEnd: Position::from(1, 1),
            expectedContents: '',
            actualBuffer: $buffer
        );
    }

    /**
     * @test
     */
    public function resetEmptiesTheContentsAndRestoresBoundingPositions(): void
    {
        $buffer = new Buffer();
        $buffer->append('A');
        $buffer->append('B');
        $buffer->append('C');

        $buffer->reset();

        $this->assertBufferState(
            expectedStart: Position::from(0, 0),
            expectedEnd: Position::from(0, 0),
            expectedContents: '',
            actualBuffer: $buffer
        );

        $buffer = new Buffer();
        $buffer->append('A');
        $buffer->append('B');

        $buffer->flush();

        $buffer->append('C');
        $buffer->append('D');

        $buffer->reset();

        $buffer->append('E');
        $buffer->append('F');

        $this->assertBufferState(
            expectedStart: Position::from(0, 2),
            expectedEnd: Position::from(0, 3),
            expectedContents: 'EF',
            actualBuffer: $buffer
        );
    }

    public static function assertBufferState(
        Position $expectedStart,
        Position $expectedEnd,
        string $expectedContents,
        Buffer $actualBuffer,
        string $message = ''
    ): void {
        $prefix = $message ? $message . ': ' : '';

        self::assertEquals(
            $expectedStart,
            $actualBuffer->getStart(),
            $prefix . 'Start position of buffer is incorrect.'
        );

        self::assertEquals(
            $expectedEnd,
            $actualBuffer->getEnd(),
            $prefix . 'End position of buffer is incorrect.'
        );

        self::assertEquals(
            Range::from($expectedStart, $expectedEnd),
            $actualBuffer->getRange(),
            $prefix . 'Range of buffer is incorrect.'
        );

        self::assertEquals(
            $expectedContents,
            $actualBuffer->getContents(),
            $prefix . 'Contents of buffer are incorrect.'
        );
    }
}
