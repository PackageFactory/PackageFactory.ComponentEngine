<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\StringLiteral;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;
use PHPUnit\Framework\TestCase;

final class StringLiteralTest extends TestCase
{
    /**
     * @return array<string, array{string, string, array<mixed>}>
     */
    public function provider(): array 
    {
        return [
            'double-quote simple' => [
                '"Hello World!"',
                'Hello World!',
                [
                    'type' => 'StringLiteral',
                    'offset' => [0, 13],
                    'value' => 'Hello World!'
                ]
            ],
            'double-quote with escapes' => [
                '"Hello \"World\""',
                'Hello "World"',
                [
                    'type' => 'StringLiteral',
                    'offset' => [0, 16],
                    'value' => 'Hello "World"'
                ]
            ],
            'single-quote simple' => [
                '\'Hello World\'',
                'Hello World',
                [
                    'type' => 'StringLiteral',
                    'offset' => [0, 12],
                    'value' => 'Hello World'
                ]
            ],
            'single-quote with escapes' => [
                '\'Hello \\\'World\\\'\'',
                'Hello \'World\'',
                [
                    'type' => 'StringLiteral',
                    'offset' => [0, 16],
                    'value' => 'Hello \'World\''
                ]
            ],
            'double-quote exit after delimiter' => [
                '"Hello World" ',
                'Hello World',
                [
                    'type' => 'StringLiteral',
                    'offset' => [0, 12],
                    'value' => 'Hello World'
                ]
            ],
            'single-quote exit after delimiter' => [
                '\'Hello World\' ',
                'Hello World',
                [
                    'type' => 'StringLiteral',
                    'offset' => [0, 12],
                    'value' => 'Hello World'
                ]
            ],
        ];
    }

    /**
     * @dataProvider provider
     * @test
     * @small
     * @param string $input
     * @param string $output
     * @param array<mixed> $asJson
     * @return void
     */
    public function test(string $input, string $output, array $asJson): void
    {
        $source = Source::fromString($input);
        $tokenizer = Tokenizer::fromSource($source, Scope\StringLiteral::class);
        $stream = TokenStream::fromTokenizer($tokenizer);

        $result = StringLiteral::fromTokenStream($stream);

        $this->assertEquals($output, $result->getValue());
        $this->assertEquals($output, $result->__toString());
        $this->assertJsonStringEqualsJsonString(
            (string) json_encode($asJson),
            (string) json_encode($result)
        );
    }
}