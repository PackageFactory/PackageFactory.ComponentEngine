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
     * @return array
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
     * @param string $input
     * @param string $output
     * @param array $asJson
     * @return void
     */
    public function test(string $input, string $output, array $asJson): void
    {
        $source = Source::createFromString($input);
        $tokenizer = Tokenizer::createFromSource($source, Scope\StringLiteral::class);
        $stream = TokenStream::createFromTokenizer($tokenizer);

        $result = StringLiteral::createFromTokenStream($stream);
        
        $this->assertEquals($output, $result->getValue());
        $this->assertEquals($output, $result->__toString());
        $this->assertEquals($output, $result->evaluate());
        $this->assertJsonStringEqualsJsonString(
            json_encode($asJson),
            json_encode($result)
        );
    }
}