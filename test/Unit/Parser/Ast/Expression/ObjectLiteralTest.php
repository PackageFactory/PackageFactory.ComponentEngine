<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\ObjectLiteral;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PHPUnit\Framework\TestCase;

final class ObjectLiteralTest extends TestCase
{
    /**
     * @return array
     */
    public function provider(): array 
    {
        return [
            'empty object' => [
                '{}',
                (object) [],
                [
                    'type' => 'ObjectLiteral',
                    'offset' => [0, 1],
                    'properties' => [
                    ]
                ]
            ],
            'number property' => [
                '{ number: 42 }',
                (object) [ 'number' => 42.0 ],
                [
                    'type' => 'ObjectLiteral',
                    'offset' => [0, 13],
                    'properties' => [
                        [
                            'key' => [
                                'type' => 'Identifier',
                                'offset' => [2, 7],
                                'value' => 'number'
                            ],
                            'value' => [
                                'type' => 'NumberLiteral',
                                'offset' => [10, 11],
                                'value' => '42'
                            ]
                        ]
                    ]
                ]
            ],
            'string property' => [
                '{ string: "Hello World!" }',
                (object) [ 'string' => 'Hello World!' ],
                [
                    'type' => 'ObjectLiteral',
                    'offset' => [0, 25],
                    'properties' => [
                        [
                            'key' => [
                                'type' => 'Identifier',
                                'offset' => [2, 7],
                                'value' => 'string'
                            ],
                            'value' => [
                                'type' => 'StringLiteral',
                                'offset' => [10, 23],
                                'value' => 'Hello World!'
                            ]
                        ]
                    ]
                ]
            ],
            'boolean properties' => [
                '{ isTrue: true, isFalse: false }',
                (object) [ 'isTrue' => true, 'isFalse' => false ],
                [
                    'type' => 'ObjectLiteral',
                    'offset' => [0, 31],
                    'properties' => [
                        [
                            'key' => [
                                'type' => 'Identifier',
                                'offset' => [2, 7],
                                'value' => 'isTrue'
                            ],
                            'value' => [
                                'type' => 'BooleanLiteral',
                                'offset' => [10, 13],
                                'value' => 'true'
                            ]
                        ],
                        [
                            'key' => [
                                'type' => 'Identifier',
                                'offset' => [16, 22],
                                'value' => 'isFalse'
                            ],
                            'value' => [
                                'type' => 'BooleanLiteral',
                                'offset' => [25, 29],
                                'value' => 'false'
                            ]
                        ],
                    ]
                ]
            ],
            'null property' => [
                '{ isNull: null }',
                (object) [],
                [
                    'type' => 'ObjectLiteral',
                    'offset' => [0, 15],
                    'properties' => [
                        [
                            'key' => [
                                'type' => 'Identifier',
                                'offset' => [2, 7],
                                'value' => 'isNull'
                            ],
                            'value' => [
                                'type' => 'NullLiteral',
                                'offset' => [10, 13]
                            ]
                        ],
                    ]
                ]
            ],
            'mixed' => [
                '{ isHighlighted: true, title: "Latest News", fontSize: 12.3, content: null }',
                (object) [ 'isHighlighted' => true, 'title' => 'Latest News', 'fontSize' => 12.3 ],
                [
                    'type' => 'ObjectLiteral',
                    'offset' => [0, 75],
                    'properties' => [
                        [
                            'key' => [
                                'type' => 'Identifier',
                                'offset' => [2, 14],
                                'value' => 'isHighlighted'
                            ],
                            'value' => [
                                'type' => 'BooleanLiteral',
                                'offset' => [17, 20],
                                'value' => 'true'
                            ]
                        ],
                        [
                            'key' => [
                                'type' => 'Identifier',
                                'offset' => [23, 27],
                                'value' => 'title'
                            ],
                            'value' => [
                                'type' => 'StringLiteral',
                                'offset' => [30, 42],
                                'value' => 'Latest News'
                            ]
                        ],
                        [
                            'key' => [
                                'type' => 'Identifier',
                                'offset' => [45, 52],
                                'value' => 'fontSize'
                            ],
                            'value' => [
                                'type' => 'NumberLiteral',
                                'offset' => [55, 58],
                                'value' => '12.3'
                            ]
                        ],
                        [
                            'key' => [
                                'type' => 'Identifier',
                                'offset' => [61, 67],
                                'value' => 'content'
                            ],
                            'value' => [
                                'type' => 'NullLiteral',
                                'offset' => [70, 73]
                            ]
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * @dataProvider provider
     * @test
     * @return void
     */
    public function test(string $input, \stdClass $asObject, array $asJson): void
    {
        $source = Source::createFromString($input);
        $tokenizer = Tokenizer::createFromSource($source, Scope\Expression::class);
        $stream = TokenStream::createFromTokenizer($tokenizer);

        $result = ObjectLiteral::createFromTokenStream($stream);

        $this->assertEquals($asObject, $result->evaluate());
        $this->assertJsonStringEqualsJsonString(
            json_encode($asJson),
            json_encode($result)
        );
    }
}