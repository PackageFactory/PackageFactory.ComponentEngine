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
     * @return array
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
     * @param string $input
     * @param string $output
     * @param array $asJson
     * @return void
     */
    public function test(string $input, string $output, array $asJson): void
    {
        $source = Source::createFromString($input);
        $tokenizer = Tokenizer::createFromSource($source, Scope\Identifier::class);
        $stream = TokenStream::createFromTokenizer($tokenizer);

        $result = Identifier::createFromTokenStream($stream);
        
        $this->assertEquals($output, $result->getValue());
        $this->assertEquals($output, $result->__toString());
        $this->assertJsonStringEqualsJsonString(
            json_encode($asJson),
            json_encode($result)
        );
    }
}