<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PHPUnit\Framework\TestCase;

final class DashOperationTest extends TestCase
{
    /**
     * @return array<string, array{string, array<mixed>}>
     */
    public function provider(): array 
    {
        return [
            'primitive addition' => [
                '1 + 1',
                [
                    'type' => 'DashOperation',
                    'left' => [
                        'type' => 'NumberLiteral',
                        'offset' => [0, 0],
                        'value' => '1'
                    ],
                    'operator' => '+',
                    'right' => [
                        'type' => 'NumberLiteral',
                        'offset' => [4, 4],
                        'value' => '1'
                    ],
                ]
            ],
            'primitive subtraction' => [
                '12.5 - 13',
                [
                    'type' => 'DashOperation',
                    'left' => [
                        'type' => 'NumberLiteral',
                        'offset' => [0, 3],
                        'value' => '12.5'
                    ],
                    'operator' => '-',
                    'right' => [
                        'type' => 'NumberLiteral',
                        'offset' => [7, 8],
                        'value' => '13'
                    ],
                ]
            ],
            'string concatenation' => [
                '"Hello " + "World!"',
                [
                    'type' => 'DashOperation',
                    'left' => [
                        'type' => 'StringLiteral',
                        'offset' => [0, 7],
                        'value' => 'Hello '
                    ],
                    'operator' => '+',
                    'right' => [
                        'type' => 'StringLiteral',
                        'offset' => [11, 18],
                        'value' => 'World!'
                    ],
                ]
            ],
            'subtraction with three operands' => [
                '12.5 - 13 - 42',
                [
                    'type' => 'DashOperation',
                    'left' => [
                        'type' => 'DashOperation',
                        'left' => [
                            'type' => 'NumberLiteral',
                            'offset' => [0, 3],
                            'value' => '12.5'
                        ],
                        'operator' => '-',
                        'right' => [
                            'type' => 'NumberLiteral',
                            'offset' => [7, 8],
                            'value' => '13'
                        ],
                    ],
                    'operator' => '-',
                    'right' => [
                        'type' => 'NumberLiteral',
                        'offset' => [12, 13],
                        'value' => '42'
                    ],
                ]
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
        $result = ExpressionParser::parse($stream);

        $this->assertJsonStringEqualsJsonString(
            (string) json_encode($asJson),
            (string) json_encode($result)
        );
    }
}