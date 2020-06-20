<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Lexer\Scope;

use PackageFactory\ComponentEngine\Parser\Lexer\Scope\Whitespace;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;
use PackageFactory\ComponentEngine\Test\Util\TokenizerTestTrait;
use PHPUnit\Framework\TestCase;

final class WhitespaceTest extends TestCase
{
    use TokenizerTestTrait;

    /**
     * @test
     * @return void
     */
    public function spaces(): void
    {
        $single = SourceIterator::createFromSource(Source::createFromString(' '));
        
        $this->assertTokenStream([
            [TokenType::WHITESPACE(), ' ']
        ], Whitespace::tokenize($single));
        
        $multiple = SourceIterator::createFromSource(Source::createFromString('    '));

        $this->assertTokenStream([
            [TokenType::WHITESPACE(), '    ']
        ], Whitespace::tokenize($multiple));
    }

    /**
     * @test
     * @return void
     */
    public function tabs(): void
    {
        $single = SourceIterator::createFromSource(Source::createFromString('	'));
        
        $this->assertTokenStream([
            [TokenType::WHITESPACE(), '	']
        ], Whitespace::tokenize($single));

        $multiple = SourceIterator::createFromSource(Source::createFromString('		'));
        
        $this->assertTokenStream([
            [TokenType::WHITESPACE(), '		']
        ], Whitespace::tokenize($multiple));
    }

    /**
     * @test
     * @return void
     */
    public function newline(): void
    {
        $single = SourceIterator::createFromSource(Source::createFromString(PHP_EOL));
        
        $this->assertTokenStream([
            [TokenType::END_OF_LINE(), PHP_EOL]
        ], Whitespace::tokenize($single));

        $multiple = SourceIterator::createFromSource(Source::createFromString(PHP_EOL . PHP_EOL));
        
        $this->assertTokenStream([
            [TokenType::END_OF_LINE(), PHP_EOL],
            [TokenType::END_OF_LINE(), PHP_EOL]
        ], Whitespace::tokenize($multiple));
    }

    /**
     * @test
     * @return void
     */
    public function mixed(): void
    {
        $tabsAndSpaces = SourceIterator::createFromSource(Source::createFromString('	   	   '));
        
        $this->assertTokenStream([
            [TokenType::WHITESPACE(), '	   	   ']
        ], Whitespace::tokenize($tabsAndSpaces));

        $newLineAndSpaces = SourceIterator::createFromSource(Source::createFromString(PHP_EOL . '    ' . PHP_EOL));
        
        $this->assertTokenStream([
            [TokenType::END_OF_LINE(), PHP_EOL],
            [TokenType::WHITESPACE(), '    '],
            [TokenType::END_OF_LINE(), PHP_EOL],
        ], Whitespace::tokenize($newLineAndSpaces));

        $newLineAndTabs = SourceIterator::createFromSource(Source::createFromString(PHP_EOL . '	' . PHP_EOL));
        
        $this->assertTokenStream([
            [TokenType::END_OF_LINE(), PHP_EOL],
            [TokenType::WHITESPACE(), '	'],
            [TokenType::END_OF_LINE(), PHP_EOL],
        ], Whitespace::tokenize($newLineAndTabs));

        $all = SourceIterator::createFromSource(Source::createFromString('	' . PHP_EOL . '   ' . PHP_EOL . '	   '));

        $this->assertTokenStream([
            [TokenType::WHITESPACE(), '	'],
            [TokenType::END_OF_LINE(), PHP_EOL],
            [TokenType::WHITESPACE(), '   '],
            [TokenType::END_OF_LINE(), PHP_EOL],
            [TokenType::WHITESPACE(), '	   '],
        ], Whitespace::tokenize($all));
    }
}