<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Lexer\Scope;

use PackageFactory\ComponentEngine\Parser\Lexer\Debug\Printer;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope\Comment;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;
use PackageFactory\ComponentEngine\Test\Util\TokenizerTestTrait;
use PHPUnit\Framework\TestCase;

final class CommentTest extends TestCase
{
    use TokenizerTestTrait;

    /**
     * @return array<string, array{string, array<int, array{TokenType, string}>}>
     */
    public function provider(): array
    {
        return [
            'single line' => [
                '// This is a comment',
                [
                    [TokenType::COMMENT_START(), '//'],
                    [TokenType::COMMENT_CONTENT(), ' This is a comment'],
                ]
            ],
            'single line and line break' => [
                '// This is a comment' . PHP_EOL,
                [
                    [TokenType::COMMENT_START(), '//'],
                    [TokenType::COMMENT_CONTENT(), ' This is a comment'],
                ]
            ],
            'multi-line' => [
                '/* This is a comment */',
                [
                    [TokenType::COMMENT_START(), '/*'],
                    [TokenType::COMMENT_CONTENT(), ' This is a comment '],
                    [TokenType::COMMENT_END(), '*/'],
                ]
            ],
            'multi-line exit after delimiter' => [
                '/* This is a comment */ This is not a comment anymore',
                [
                    [TokenType::COMMENT_START(), '/*'],
                    [TokenType::COMMENT_CONTENT(), ' This is a comment '],
                    [TokenType::COMMENT_END(), '*/'],
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
        $this->assertTokenStream($tokens, Comment::tokenize($iterator));
    }
}