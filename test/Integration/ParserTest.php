<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Integration;

use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Parser;

final class ParserTest extends BaseTestCase
{
    /**
     * @return iterable<string, array<int, string>>
     */
    public function basics(): iterable
    {
        foreach ($this->fixtures('basics') as $key => $value) {
            yield $key => $value;
        }
    }

    /**
     * @test
     * @dataProvider basics
     */
    public function testBasics(string $filename): void
    {
        $source = Source::createFromFile($filename);
        $tokenizer = Tokenizer::createFromSource($source);
        $parser = Parser::createFromTokenizer($tokenizer);

        $this->assertMatchesJsonSnapshot(
            json_encode($parser->parse(), JSON_PRETTY_PRINT)
        );
    }

    /**
     * @return iterable<string, array<int, string>>
     */
    public function variables(): iterable
    {
        foreach ($this->fixtures('variables') as $key => $value) {
            yield $key => $value;
        }
    }

    /**
     * @test
     * @dataProvider variables
     */
    public function testVariables(string $filename): void
    {
        $source = Source::createFromFile($filename);
        $tokenizer = Tokenizer::createFromSource($source);
        $parser = Parser::createFromTokenizer($tokenizer);

        $this->assertMatchesJsonSnapshot(
            json_encode($parser->parse(), JSON_PRETTY_PRINT)
        );
    }

    /**
     * @return iterable<string, array<int, string>>
     */
    public function specials(): iterable
    {
        foreach ($this->fixtures('specials') as $key => $value) {
            yield $key => $value;
        }
    }

    /**
     * @test
     * @dataProvider specials
     */
    public function testSpecials(string $filename): void
    {
        $source = Source::createFromFile($filename);
        $tokenizer = Tokenizer::createFromSource($source);
        $parser = Parser::createFromTokenizer($tokenizer);

        $this->assertMatchesJsonSnapshot(
            json_encode($parser->parse(), JSON_PRETTY_PRINT)
        );
    }

    /**
     * @return iterable<string, array<int, string>>
     */
    public function modules(): iterable
    {
        foreach ($this->fixtures('modules') as $key => $value) {
            yield $key => $value;
        }
    }

    /**
     * @test
     * @dataProvider modules
     */
    public function testModules(string $filename): void
    {
        $source = Source::createFromFile($filename);
        $tokenizer = Tokenizer::createFromSource($source);
        $parser = Parser::createFromTokenizer($tokenizer);

        $this->assertMatchesJsonSnapshot(
            json_encode($parser->parse(), JSON_PRETTY_PRINT)
        );
    }
}