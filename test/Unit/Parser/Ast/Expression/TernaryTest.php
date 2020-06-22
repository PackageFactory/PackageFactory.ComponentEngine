<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Expression;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Ternary;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;
use PHPUnit\Framework\TestCase;

final class TernaryTest extends TestCase
{
    /**
     * @return array<string, array{string, string, array<mixed>}>
     */
    public function provider(): array 
    {
        return [
            'true ? "yes" : "no"' => [
                'true ? "yes" : "no"',
                'yes',
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
                'no',
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
                'yes',
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
                'no',
                [
                    'type' => 'Ternary',
                    'condition' => [
                        'type' => 'Conjunction',
                        'left' => [
                            'offset' => [0, 3],
                            'type' => 'BooleanLiteral',
                            'value' => 'true'
                        ],
                        'operator' => '&&',
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
     * @param string $input
     * @param string $output
     * @param array<mixed> $asJson
     * @return void
     */
    public function test(string $input, string $output, array $asJson): void
    {
        $source = Source::createFromString($input);
        $tokenizer = Tokenizer::createFromSource($source, Scope\Expression::class);
        $stream = TokenStream::createFromTokenizer($tokenizer);

        $result = Expression::createFromTokenStream($stream);

        $this->assertNotNull($result);
        if ($result !== null) {
            $this->assertEquals($output, $result->evaluate());
        }
        $this->assertJsonStringEqualsJsonString(
            (string) json_encode($asJson),
            (string) json_encode($result)
        );
    }
}