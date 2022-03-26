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

use PackageFactory\ComponentEngine\Parser\Ast\Expression\ArrayLiteral;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PHPUnit\Framework\TestCase;

final class ArrayLiteralTest extends TestCase
{
    /**
     * @return array<string, array{string, array<mixed>}>
     */
    public function provider(): array
    {
        return [
            'empty array' => [
                '[]',
                [
                    'type' => 'ArrayLiteral',
                    'offset' => [0, 1],
                    'items' => []
                ],
            ],
            'one number element' => [
                '[42]',
                [
                    'type' => 'ArrayLiteral',
                    'offset' => [0, 3],
                    'items' => [
                        [
                            'type' => 'NumberLiteral',
                            'offset' => [1, 2],
                            'value' => '42'
                        ]
                    ]
                ],
            ],
            'boolean elements' => [
                '[true, false]',
                [
                    'type' => 'ArrayLiteral',
                    'offset' => [0, 12],
                    'items' => [
                        [
                            'type' => 'BooleanLiteral',
                            'offset' => [1, 4],
                            'value' => 'true'
                        ],
                        [
                            'type' => 'BooleanLiteral',
                            'offset' => [7, 11],
                            'value' => 'false'
                        ]
                    ]
                ],
            ],
            'null elements' => [
                '[null, null, null]',
                [
                    'type' => 'ArrayLiteral',
                    'offset' => [0, 17],
                    'items' => [
                        [
                            'type' => 'NullLiteral',
                            'offset' => [1, 4]
                        ],
                        [
                            'type' => 'NullLiteral',
                            'offset' => [7, 10]
                        ],
                        [
                            'type' => 'NullLiteral',
                            'offset' => [13, 16]
                        ],
                    ]
                ],
            ],
            'string elements' => [
                '["foo", "bar", "baz"]',
                [
                    'type' => 'ArrayLiteral',
                    'offset' => [0, 20],
                    'items' => [
                        [
                            'type' => 'StringLiteral',
                            'offset' => [1, 5],
                            'value' => 'foo'
                        ],
                        [
                            'type' => 'StringLiteral',
                            'offset' => [8, 12],
                            'value' => 'bar'
                        ],
                        [
                            'type' => 'StringLiteral',
                            'offset' => [15, 19],
                            'value' => 'baz'
                        ],
                    ]
                ],
            ],
            'mixed' => [
                '[.5, "Hello World", false, 12.3, null]',
                [
                    'type' => 'ArrayLiteral',
                    'offset' => [0, 37],
                    'items' => [
                        [
                            'type' => 'NumberLiteral',
                            'offset' => [1, 2],
                            'value' => '.5'
                        ],
                        [
                            'type' => 'StringLiteral',
                            'offset' => [5, 17],
                            'value' => 'Hello World'
                        ],
                        [
                            'type' => 'BooleanLiteral',
                            'offset' => [20, 24],
                            'value' => 'false'
                        ],
                        [
                            'type' => 'NumberLiteral',
                            'offset' => [27, 30],
                            'value' => '12.3'
                        ],
                        [
                            'type' => 'NullLiteral',
                            'offset' => [33, 36]
                        ],
                    ]
                ],
            ],
        ];
    }

    /**
     * @dataProvider provider
     * @test
     * @small
     * @param string $input
     * @param array<mixed> $asJson
     * @return void
     */
    public function test(string $input, array $asJson): void
    {
        $source = Source::fromString($input);
        $tokenizer = Tokenizer::fromSource($source, Scope\Expression::class);
        $stream = TokenStream::fromTokenizer($tokenizer);

        $result = ArrayLiteral::fromTokenStream($stream);

        $this->assertJsonStringEqualsJsonString(
            (string) json_encode($asJson),
            (string) json_encode($result)
        );
    }
}
