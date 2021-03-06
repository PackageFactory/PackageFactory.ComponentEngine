<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Negation;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PHPUnit\Framework\TestCase;

final class NegationTest extends TestCase
{
    /**
     * @return array<string, array{string, array<mixed>}>
     */
    public function provider(): array 
    {
        return [
            'primitive negation' => [
                '!true',
                [
                    'type' => 'Negation',
                    'offset' => [0, 0],
                    'subject' => [
                        'type' => 'BooleanLiteral',
                        'offset' => [1, 4],
                        'value' => 'true'
                    ]
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