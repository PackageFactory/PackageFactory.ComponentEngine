<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\NullLiteral;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PHPUnit\Framework\TestCase;

final class NullLiteralTest extends TestCase
{
    /**
     * @return array<string, array{string, string, array<mixed>}>
     */
    public function provider(): array 
    {
        return [
            'null' => [
                'null',
                'null',
                [
                    'type' => 'NullLiteral',
                    'offset' => [0, 3]
                ]
            ],
        ];
    }

    /**
     * @dataProvider provider
     * @test
     * @small
     * @param string $input
     * @param string $asString
     * @param array<mixed> $asJson
     * @return void
     */
    public function test(string $input, string $asString, array $asJson): void
    {
        $source = Source::fromString($input);
        $tokenizer = Tokenizer::fromSource($source, Scope\Expression::class);
        $stream = TokenStream::fromTokenizer($tokenizer);

        $result = NullLiteral::fromTokenStream($stream);
        
        $this->assertEquals($asString, $result->getValue());
        $this->assertEquals($asString, $result->__toString());
        $this->assertJsonStringEqualsJsonString(
            (string) json_encode($asJson),
            (string) json_encode($result)
        );
    }
}