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
     * @return array<string, array{string, string, null, array<mixed>}>
     */
    public function provider(): array 
    {
        return [
            'null' => [
                'null',
                'null',
                null,
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
     * @param string $input
     * @param string $asString
     * @param null $asNull
     * @param array<mixed> $asJson
     * @return void
     */
    public function test(string $input, string $asString, $asNull, array $asJson): void
    {
        $source = Source::createFromString($input);
        $tokenizer = Tokenizer::createFromSource($source, Scope\Expression::class);
        $stream = TokenStream::createFromTokenizer($tokenizer);

        $result = NullLiteral::createFromTokenStream($stream);
        
        $this->assertEquals($asString, $result->getValue());
        $this->assertEquals($asString, $result->__toString());
        $this->assertEquals($asNull, $result->evaluate());
        $this->assertJsonStringEqualsJsonString(
            (string) json_encode($asJson),
            (string) json_encode($result)
        );
    }
}