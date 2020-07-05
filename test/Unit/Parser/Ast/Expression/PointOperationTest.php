<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\PointOperation;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PHPUnit\Framework\TestCase;

final class PointOperationTest extends TestCase
{
    /**
     * @return array<string, array{string, array<mixed>}>
     */
    public function provider(): array 
    {
        return [
            'primitive multiplication' => [
                '2 * 2',
                [
                    'type' => 'PointOperation',
                    'left' => [
                        'type' => 'NumberLiteral',
                        'offset' => [0, 0],
                        'value' => '2'
                    ],
                    'operator' => '*',
                    'right' => [
                        'type' => 'NumberLiteral',
                        'offset' => [4, 4],
                        'value' => '2'
                    ],
                ]
            ],
            'primitive division' => [
                '12 / .5',
                [
                    'type' => 'PointOperation',
                    'left' => [
                        'type' => 'NumberLiteral',
                        'offset' => [0, 1],
                        'value' => '12'
                    ],
                    'operator' => '/',
                    'right' => [
                        'type' => 'NumberLiteral',
                        'offset' => [5, 6],
                        'value' => '.5'
                    ],
                ]
            ],
            'primitive modulo' => [
                '13 % 10',
                [
                    'type' => 'PointOperation',
                    'left' => [
                        'type' => 'NumberLiteral',
                        'offset' => [0, 1],
                        'value' => '13'
                    ],
                    'operator' => '%',
                    'right' => [
                        'type' => 'NumberLiteral',
                        'offset' => [5, 6],
                        'value' => '10'
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