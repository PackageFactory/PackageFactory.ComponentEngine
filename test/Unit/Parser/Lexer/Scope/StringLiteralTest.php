<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Lexer\Scope;

use PackageFactory\ComponentEngine\Parser\Lexer\Debug\Printer;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope\StringLiteral;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;
use PackageFactory\ComponentEngine\Test\Util\TokenizerTestTrait;
use PHPUnit\Framework\TestCase;

final class StringLiteralTest extends TestCase
{
    use TokenizerTestTrait;

    /**
     * @return array<string, array{string, array<int, array{TokenType, string}>}>
     */
    public function provider(): array
    {
        return [
            'double-quote simple' => [
                '"Hello World"',
                [
                    [TokenType::STRING_LITERAL_START(), '"'],
                    [TokenType::STRING_LITERAL_CONTENT(), 'Hello World'],
                    [TokenType::STRING_LITERAL_END(), '"'],
                ]
            ],
            'double-quote with escapes' => [
                '"Hello \"World\""',
                [
                    [TokenType::STRING_LITERAL_START(), '"'],
                    [TokenType::STRING_LITERAL_CONTENT(), 'Hello '],
                    [TokenType::STRING_LITERAL_ESCAPE(), '\\'],
                    [TokenType::STRING_LITERAL_ESCAPED_CHARACTER(), '"'],
                    [TokenType::STRING_LITERAL_CONTENT(), 'World'],
                    [TokenType::STRING_LITERAL_ESCAPE(), '\\'],
                    [TokenType::STRING_LITERAL_ESCAPED_CHARACTER(), '"'],
                    [TokenType::STRING_LITERAL_END(), '"'],
                ]
            ],
            'single-quote simple' => [
                '\'Hello World\'',
                [
                    [TokenType::STRING_LITERAL_START(), '\''],
                    [TokenType::STRING_LITERAL_CONTENT(), 'Hello World'],
                    [TokenType::STRING_LITERAL_END(), '\''],
                ]
            ],
            'single-quote with escapes' => [
                '\'Hello \\\'World\\\'\'',
                [
                    [TokenType::STRING_LITERAL_START(), '\''],
                    [TokenType::STRING_LITERAL_CONTENT(), 'Hello '],
                    [TokenType::STRING_LITERAL_ESCAPE(), '\\'],
                    [TokenType::STRING_LITERAL_ESCAPED_CHARACTER(), '\''],
                    [TokenType::STRING_LITERAL_CONTENT(), 'World'],
                    [TokenType::STRING_LITERAL_ESCAPE(), '\\'],
                    [TokenType::STRING_LITERAL_ESCAPED_CHARACTER(), '\''],
                    [TokenType::STRING_LITERAL_END(), '\''],
                ]
            ],
            'double-quote exit after delimiter' => [
                '"Hello World" ',
                [
                    [TokenType::STRING_LITERAL_START(), '"'],
                    [TokenType::STRING_LITERAL_CONTENT(), 'Hello World'],
                    [TokenType::STRING_LITERAL_END(), '"'],
                ]
            ],
            'single-quote exit after delimiter' => [
                '\'Hello World\' ',
                [
                    [TokenType::STRING_LITERAL_START(), '\''],
                    [TokenType::STRING_LITERAL_CONTENT(), 'Hello World'],
                    [TokenType::STRING_LITERAL_END(), '\''],
                ]
            ],
            'double-quote exit after newline' => [
                '"Hello ' . PHP_EOL . ' World"',
                [
                    [TokenType::STRING_LITERAL_START(), '"'],
                    [TokenType::STRING_LITERAL_CONTENT(), 'Hello '],
                ]
            ],
            'single-quote exit after newline' => [
                '\'Hello ' . PHP_EOL . ' World\'',
                [
                    [TokenType::STRING_LITERAL_START(), '\''],
                    [TokenType::STRING_LITERAL_CONTENT(), 'Hello '],
                ]
            ],
            'without delimiter' => [
                'Hello World',
                [
                    [TokenType::STRING_LITERAL_CONTENT(), 'Hello World'],
                ]
            ],
        ];
    }

    /**
     * @dataProvider provider
     * @test
     * @param string $input
     * @param array<int, array{TokenType, string}> $tokens
     * @return void
     */
    public function test(string $input, array $tokens): void
    {
        $iterator = SourceIterator::createFromSource(Source::createFromString($input));
        $this->assertTokenStream($tokens, StringLiteral::tokenize($iterator));
    }
}