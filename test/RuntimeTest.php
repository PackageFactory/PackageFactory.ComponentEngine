<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test;

use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Parser;
use PackageFactory\ComponentEngine\Runtime\Runtime;
use PackageFactory\ComponentEngine\Runtime\Context;

final class RuntimeTest extends BaseTestCase
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
        $runtime = Runtime::createFromModule($parser->parse());

        $this->assertMatchesSnapshot(
            (string) $runtime->evaluate(Context::createEmpty())
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
        $runtime = Runtime::createFromModule($parser->parse());

        $this->assertMatchesSnapshot(
            (string) $runtime->evaluate(Context::createFromArray([
                'class' => 'exampleClass-1',
                'headerStyle' => 'background-color: lightgray; padding: 1em;',
                'name' => 'Example name #1',
                'headline' => 'Example Headline #1',
                'props' => [
                    'class' => 'exampleClass-2 from-props',
                    'name' => 'Example name #2 from props',
                    'headline' => 'Example Headline #2 from props',
                    'content' => 'Lorem ipsum dolor sit amet... (From props)'
                ]
            ]))
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
        $runtime = Runtime::createFromModule($parser->parse());

        $this->assertMatchesSnapshot(
            (string) $runtime->evaluate(Context::createFromArray([
                'props' => [
                    'items' => [
                        'Item #1 from props',
                        'Item #2 from props',
                        'Item #3 from props',
                        'Item #4 from props',
                        'Item #5 from props'
                    ],
                    'content' => 'Lorem ipsum dolor sit amet... (From props)',
                    'hasHeadline' => false,
                    'headline' => 'Example Headline from props'
                ]
            ]))
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
        $runtime = Runtime::createFromModule($parser->parse());

        $this->assertMatchesSnapshot(
            (string) $runtime->evaluate(Context::createFromArray([
                'props' => [
                    'label' => 'Label from test props',
                    'children' => 'Children from test props'
                ]
            ]))
        );
    }
}