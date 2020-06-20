<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Lexer\Scope;

use PackageFactory\ComponentEngine\Parser\Lexer\Scope\Number;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;
use PackageFactory\ComponentEngine\Test\Util\TokenizerTestTrait;
use PHPUnit\Framework\TestCase;

final class NumberTest extends TestCase
{
    use TokenizerTestTrait;

    /**
     * @return array<string, array<int, string>>
     */
    public function provider(): array
    {
        return [
            ' 0' => ['0'],
            ' 1234567890' => ['1234567890'],
            ' 42' => ['42'],
            ' 0b10000000000000000000000000000000' => ['0b10000000000000000000000000000000'],
            ' 0b01111111100000000000000000000000' => ['0b01111111100000000000000000000000'],
            ' 0B00000000011111111111111111111111' => ['0B00000000011111111111111111111111'],
            ' 0o755' => ['0o755'],
            ' 0o644' => ['0o644'],
            ' 0xFFFFFFFFFFFFFFFFF' => ['0xFFFFFFFFFFFFFFFFF'],
            ' 0x123456789ABCDEF' => ['0x123456789ABCDEF'],
            ' 0xA' => ['0xA'],
            ' 1E3' => ['1E3'],
            ' 2e6' => ['2e6'],
            ' 0.1e2' => ['0.1e2'],
            ' .22' => ['.22'],
        ];
    }

    /**
     * @dataProvider provider
     * @test
     * @param string $number
     * @return void
     */
    public function test(string $number): void
    {
        $iterator = SourceIterator::createFromSource(Source::createFromString($number));

        $this->assertTokenStream([
            [TokenType::NUMBER(), $number]
        ], Number::tokenize($iterator));
    }
}