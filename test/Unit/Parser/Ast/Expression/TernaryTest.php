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

use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;
use PHPUnit\Framework\TestCase;

final class TernaryTest extends TestCase
{
    /**
     * @return array<string, array{string, array<mixed>}>
     */
    public function provider(): array
    {
        return [
            'true ? "yes" : "no"' => [
                'true ? "yes" : "no"',
                [
                    'type' => 'Ternary',
                    'condition' => [
                        'offset' => [0, 3],
                        'type' => 'BooleanLiteral',
                        'value' => 'true'
                    ],
                    'trueBranch' => [
                        'offset' => [7, 11],
                        'type' => 'StringLiteral',
                        'value' => 'yes'
                    ],
                    'falseBranch' => [
                        'offset' => [15, 18],
                        'type' => 'StringLiteral',
                        'value' => 'no'
                    ],
                ],
            ],
            'false ? "yes" : "no"' => [
                'false ? "yes" : "no"',
                [
                    'type' => 'Ternary',
                    'condition' => [
                        'offset' => [0, 4],
                        'type' => 'BooleanLiteral',
                        'value' => 'false'
                    ],
                    'trueBranch' => [
                        'offset' => [8, 12],
                        'type' => 'StringLiteral',
                        'value' => 'yes'
                    ],
                    'falseBranch' => [
                        'offset' => [16, 19],
                        'type' => 'StringLiteral',
                        'value' => 'no'
                    ],
                ]
            ],
            'true || false ? "yes" : "no"' => [
                'true || false ? "yes" : "no"',
                [
                    'type' => 'Ternary',
                    'condition' => [
                        'type' => 'Disjunction',
                        'left' => [
                            'offset' => [0, 3],
                            'type' => 'BooleanLiteral',
                            'value' => 'true'
                        ],
                        'operator' => '||',
                        'right' => [
                            'offset' => [8, 12],
                            'type' => 'BooleanLiteral',
                            'value' => 'false'
                        ],
                    ],
                    'trueBranch' => [
                        'offset' => [16, 20],
                        'type' => 'StringLiteral',
                        'value' => 'yes'
                    ],
                    'falseBranch' => [
                        'offset' => [24, 27],
                        'type' => 'StringLiteral',
                        'value' => 'no'
                    ],
                ]
            ],
            'true && false ? "yes" : "no"' => [
                'true && false ? "yes" : "no"',
                [
                    'type' => 'Ternary',
                    'condition' => [
                        'type' => 'Conjunction',
                        'left' => [
                            'offset' => [0, 3],
                            'type' => 'BooleanLiteral',
                            'value' => 'true'
                        ],
                        'right' => [
                            'offset' => [8, 12],
                            'type' => 'BooleanLiteral',
                            'value' => 'false'
                        ],
                    ],
                    'trueBranch' => [
                        'offset' => [16, 20],
                        'type' => 'StringLiteral',
                        'value' => 'yes'
                    ],
                    'falseBranch' => [
                        'offset' => [24, 27],
                        'type' => 'StringLiteral',
                        'value' => 'no'
                    ],
                ]
            ]
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

        $result = ExpressionParser::parse($stream);

        $this->assertNotNull($result);
        $this->assertJsonStringEqualsJsonString(
            (string) json_encode($asJson),
            (string) json_encode($result)
        );
    }
}
