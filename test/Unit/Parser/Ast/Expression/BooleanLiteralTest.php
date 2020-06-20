<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\BooleanLiteral;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PHPUnit\Framework\TestCase;

final class BooleanLiteralTest extends TestCase
{
    /**
     * @return array
     */
    public function provider(): array 
    {
        return [
            'true' => [
                'true',
                'true',
                true,
                [
                    'type' => 'BooleanLiteral',
                    'offset' => [0, 3],
                    'value' => 'true'
                ],
            ],
            'false' => [
                'false',
                'false',
                false,
                [
                    'type' => 'BooleanLiteral',
                    'offset' => [0, 4],
                    'value' => 'false'
                ],
            ],
        ];
    }

    /**
     * @dataProvider provider
     * @test
     * @return void
     */
    public function test(string $input, string $asString, bool $asBoolean, array $asJson): void
    {
        $source = Source::createFromString($input);
        $tokenizer = Tokenizer::createFromSource($source, Scope\Expression::class);
        $stream = TokenStream::createFromTokenizer($tokenizer);

        $result = BooleanLiteral::createFromTokenStream($stream);
        
        $this->assertEquals($asString, $result->getValue());
        $this->assertEquals($asString, $result->__toString());
        $this->assertEquals($asBoolean, $result->evaluate());
        $this->assertJsonStringEqualsJsonString(
            json_encode($asJson),
            json_encode($result)
        );
    }
}