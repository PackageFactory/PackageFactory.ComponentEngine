<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\TemplateLiteral;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PHPUnit\Framework\TestCase;

final class TemplateLiteralTest extends TestCase
{
    /**
     * @return array<string, array{string, array<mixed>}>
     */
    public function provider(): array 
    {
        return [
            'simple' => [
                '`Hello World!`',
                [
                    'type' => 'TemplateLiteral',
                    'offset' => [0, 13],
                    'segments' => ['Hello World!']
                ]
            ],
            'with escapes' => [
                '`Hello \`World\``',
                [
                    'type' => 'TemplateLiteral',
                    'offset' => [0, 16],
                    'segments' => ['Hello `World`']
                ]
            ],
            'exit after delimiter' => [
                '`Hello World` ',
                [
                    'type' => 'TemplateLiteral',
                    'offset' => [0, 12],
                    'segments' => ['Hello World']
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
        $tokenizer = Tokenizer::fromSource($source, Scope\TemplateLiteral::class);
        $stream = TokenStream::fromTokenizer($tokenizer);

        $result = TemplateLiteral::fromTokenStream($stream);

        $this->assertJsonStringEqualsJsonString(
            (string) json_encode($asJson),
            (string) json_encode($result)
        );
    }
}