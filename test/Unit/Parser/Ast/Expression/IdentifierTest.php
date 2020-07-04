<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Identifier;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;
use PHPUnit\Framework\TestCase;

final class IdentifierTest extends TestCase
{
    /**
     * @return array<string, array{string, string, array<mixed>}>
     */
    public function provider(): array 
    {
        return [
            'simple' => [
                'foo',
                'foo',
                [
                    'type' => 'Identifier',
                    'offset' => [0, 2],
                    'value' => 'foo'
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
        $tokenizer = Tokenizer::fromSource($source, Scope\Identifier::class);
        $stream = TokenStream::fromTokenizer($tokenizer);

        $result = Identifier::fromTokenStream($stream);
        
        $this->assertEquals($output, $result->getValue());
        $this->assertEquals($output, $result->__toString());
        $this->assertJsonStringEqualsJsonString(
            (string) json_encode($asJson),
            (string) json_encode($result)
        );
    }
}