<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Comparison;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PHPUnit\Framework\TestCase;

final class ComparisonTest extends TestCase
{
    /**
     * @return array<string, array{string, array<mixed>}>
     */
    public function provider(): array 
    {
        return [
            'primitive equality comparison' => [
                'true === true',
                [
                    'type' => 'Comparison',
                    'left' => [
                        'type' => 'BooleanLiteral',
                        'offset' => [0, 3],
                        'value' => 'true'
                    ],
                    'operator' => '===',
                    'right' => [
                        'type' => 'BooleanLiteral',
                        'offset' => [9, 12],
                        'value' => 'true'
                    ],
                ]
            ],
            'primitive inequality comparison' => [
                'true !== false',
                [
                    'type' => 'Comparison',
                    'left' => [
                        'type' => 'BooleanLiteral',
                        'offset' => [0, 3],
                        'value' => 'true'
                    ],
                    'operator' => '!==',
                    'right' => [
                        'type' => 'BooleanLiteral',
                        'offset' => [9, 13],
                        'value' => 'false'
                    ],
                ]
            ],
            'primitive greater than comparison' => [
                '12 > 11',
                [
                    'type' => 'Comparison',
                    'left' => [
                        'type' => 'NumberLiteral',
                        'offset' => [0, 1],
                        'value' => '12'
                    ],
                    'operator' => '>',
                    'right' => [
                        'type' => 'NumberLiteral',
                        'offset' => [5, 6],
                        'value' => '11'
                    ],
                ]
            ],
            'primitive greater than or equal to comparison' => [
                '"2020-07-05" >= "2020-07-01"',
                [
                    'type' => 'Comparison',
                    'left' => [
                        'type' => 'StringLiteral',
                        'offset' => [0, 11],
                        'value' => '2020-07-05'
                    ],
                    'operator' => '>=',
                    'right' => [
                        'type' => 'StringLiteral',
                        'offset' => [16, 27],
                        'value' => '2020-07-01'
                    ],
                ]
            ],
            'primitive less than comparison' => [
                '12.5 < 42',
                [
                    'type' => 'Comparison',
                    'left' => [
                        'type' => 'NumberLiteral',
                        'offset' => [0, 3],
                        'value' => '12.5'
                    ],
                    'operator' => '<',
                    'right' => [
                        'type' => 'NumberLiteral',
                        'offset' => [7, 8],
                        'value' => '42'
                    ],
                ]
            ],
            'primitive less than or equal to comparison' => [
                '0x222222 <= null',
                [
                    'type' => 'Comparison',
                    'left' => [
                        'type' => 'NumberLiteral',
                        'offset' => [0, 7],
                        'value' => '0x222222'
                    ],
                    'operator' => '<=',
                    'right' => [
                        'type' => 'NullLiteral',
                        'offset' => [12, 15]
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