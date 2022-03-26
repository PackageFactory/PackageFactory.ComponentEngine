<?php

declare(strict_types=1);

namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\ArrayLiteral;
use PackageFactory\ComponentEngine\Parser\Ast\Module\Constant;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PHPUnit\Framework\TestCase;

final class ConstantTest extends TestCase
{
    /**
     * @return array<string, array{string, array<mixed>}>
     */
    public function provider(): array
    {
        return [
            'number constant' => [
                'const foo = 1',
                [
                    'type' => 'Constant',
                    'name' => [
                        'type' => 'Identifier',
                        'offset' => [6, 8],
                        'value' => 'foo'
                    ],
                    'value' => [
                        'type' => 'NumberLiteral',
                        'offset' => [12, 12],
                        'value' => '1'
                    ]
                ],
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
        $tokenizer = Tokenizer::fromSource($source, Scope\Module::class);
        $stream = TokenStream::fromTokenizer($tokenizer);

        $result = Constant::fromTokenStream($stream);

        $this->assertJsonStringEqualsJsonString(
            (string) json_encode($asJson),
            (string) json_encode($result)
        );
    }
}
