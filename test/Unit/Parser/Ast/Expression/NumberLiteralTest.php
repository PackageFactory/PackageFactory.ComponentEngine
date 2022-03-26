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

namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\NumberLiteral;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PHPUnit\Framework\TestCase;

final class NumberLiteralTest extends TestCase
{
    /**
     * @return array<string, array{string, string, float, array<mixed>}>
     */
    public function provider(): array
    {
        return [
            'simple decimal' => [
                '42',
                '42',
                42.0,
                [
                    'type' => 'NumberLiteral',
                    'offset' => [0, 1],
                    'value' => '42'
                ]
            ],
            'simple decimal with floating point' => [
                '12.3',
                '12.3',
                12.3,
                [
                    'type' => 'NumberLiteral',
                    'offset' => [0, 3],
                    'value' => '12.3'
                ]
            ],
            'simple decimal with leading floating point' => [
                '.5',
                '.5',
                0.5,
                [
                    'type' => 'NumberLiteral',
                    'offset' => [0, 1],
                    'value' => '.5'
                ]
            ],
            'binary with lower-case delimiter' => [
                '0b111',
                '0b111',
                7.0,
                [
                    'type' => 'NumberLiteral',
                    'offset' => [0, 4],
                    'value' => '0b111'
                ]
            ],
            'binary with upper-case delimiter' => [
                '0B111',
                '0B111',
                7.0,
                [
                    'type' => 'NumberLiteral',
                    'offset' => [0, 4],
                    'value' => '0B111'
                ]
            ],
            'octal' => [
                '0o12345670',
                '0o12345670',
                2739128.0,
                [
                    'type' => 'NumberLiteral',
                    'offset' => [0, 9],
                    'value' => '0o12345670'
                ]
            ],
            'hexadecimal' => [
                '0xFF',
                '0xFF',
                255.0,
                [
                    'type' => 'NumberLiteral',
                    'offset' => [0, 3],
                    'value' => '0xFF'
                ]
            ],
        ];
    }

    /**
     * @dataProvider provider
     * @test
     * @small
     * @param string $input
     * @param string $asString
     * @param float $asNumber
     * @param array<mixed> $asJson
     * @return void
     */
    public function test(string $input, string $asString, float $asNumber, array $asJson): void
    {
        $source = Source::fromString($input);
        $tokenizer = Tokenizer::fromSource($source, Scope\Number::class);
        $stream = TokenStream::fromTokenizer($tokenizer);

        $result = NumberLiteral::fromTokenStream($stream);

        $this->assertEquals($asString, (string) $result);
        $this->assertEquals($asNumber, $result->number);
        $this->assertJsonStringEqualsJsonString(
            (string) json_encode($asJson),
            (string) json_encode($result)
        );
    }
}
