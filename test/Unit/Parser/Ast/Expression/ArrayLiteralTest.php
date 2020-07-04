<?php declare(strict_types=1);
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
        $source = Source::createFromString($input);
        $tokenizer = Tokenizer::createFromSource($source, Scope\Expression::class);
        $stream = TokenStream::createFromTokenizer($tokenizer);

        $result = ArrayLiteral::createFromTokenStream($stream);

        $this->assertJsonStringEqualsJsonString(
            (string) json_encode($asJson),
            (string) json_encode($result)
        );
    }
}