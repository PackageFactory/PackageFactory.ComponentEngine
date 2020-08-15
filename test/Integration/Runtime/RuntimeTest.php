<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Integration\Runtime;

use PackageFactory\ComponentEngine\Parser\Ast\Module\Module;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Runtime\Context\Value\ListValue;
use PackageFactory\ComponentEngine\Runtime\Evaluation\Module\OnModule;
use PackageFactory\ComponentEngine\Runtime\Runtime;
use PackageFactory\ComponentEngine\Test\Integration\BaseTestCase;
use PackageFactory\VirtualDOM\Rendering\HTML5StringRenderer;
use PackageFactory\VirtualDOM\Model\Element;

final class RuntimeTest extends BaseTestCase
{
    /**
     * @return iterable<string, array<int, string>>
     */
    public function helloWorldProvider(): iterable
    {
        yield from $this->fixtures('hello-world');
    }

    /**
     * @test
     * @small
     * @dataProvider helloWorldProvider
     * @param string $filename
     * @return void
     */
    public function helloWorldTest(string $filename): void
    {
        $source = Source::fromFile($filename);
        $tokenizer = Tokenizer::fromSource($source);
        $stream = TokenStream::fromTokenizer($tokenizer);

        $module = Module::fromTokenStream($stream);
        $runtime = Runtime::default();

        /** @var Element $result */
        $result = OnModule::evaluate($runtime, $module)->call(ListValue::fromArray([
            [
                'title' => 'Hello World Example'
            ]
        ]), false, $runtime)->getValue();

        $this->assertMatchesSnapshot(HTML5StringRenderer::render($result));
    }

    /**
     * @return iterable<string, array<int, string>>
     */
    public function navProvider(): iterable
    {
        yield from $this->fixtures('nav');
    }

    /**
     * @test
     * @small
     * @dataProvider navProvider
     * @param string $filename
     * @return void
     */
    public function navTest(string $filename): void
    {
        $source = Source::fromFile($filename);
        $tokenizer = Tokenizer::fromSource($source);
        $stream = TokenStream::fromTokenizer($tokenizer);

        $module = Module::fromTokenStream($stream);
        $runtime = Runtime::default();

        /** @var Element $result */
        $result = OnModule::evaluate($runtime, $module)->call(ListValue::fromArray([
            [
                'items' => [
                    ['href' => '#item-1', 'label' => 'Item #1', 'items' => []],
                    ['href' => '#item-2', 'label' => 'Item #2', 'items' => [
                        ['href' => '#sub-item-1', 'label' => 'SubItem #1', 'items' => []],
                        ['href' => '#sub-item-2', 'label' => 'SubItem #2', 'items' => []],
                    ]],
                    ['href' => '#item-3', 'label' => 'Item #3', 'items' => []],
                ]
            ]
        ]), false, $runtime)->getValue();

        $this->assertMatchesSnapshot(HTML5StringRenderer::render($result));
    }

    /**
     * @return iterable<string, array<int, string>>
     */
    public function siteHeaderProvider(): iterable
    {
        yield from $this->fixtures('siteheader');
    }

    /**
     * @test
     * @small
     * @dataProvider siteHeaderProvider
     * @param string $filename
     * @return void
     */
    public function siteHeaderTest(string $filename): void
    {
        $source = Source::fromFile($filename);
        $tokenizer = Tokenizer::fromSource($source);
        $stream = TokenStream::fromTokenizer($tokenizer);

        $module = Module::fromTokenStream($stream);
        $runtime = Runtime::default();

        /** @var Element $result */
        $result = OnModule::evaluate($runtime, $module)->call(ListValue::fromArray([
            [
                'urlToHomePage' => '/',
                'navigation' => [
                    'items' => [
                        ['href' => '#item-1', 'label' => 'Item #1', 'items' => []],
                        ['href' => '#item-2', 'label' => 'Item #2', 'items' => [
                            ['href' => '#sub-item-1', 'label' => 'SubItem #1', 'items' => []],
                            ['href' => '#sub-item-2', 'label' => 'SubItem #2', 'items' => []],
                        ]],
                        ['href' => '#item-3', 'label' => 'Item #3', 'items' => []],
                    ]
                ]
            ]
        ]), false, $runtime)->getValue();

        $this->assertMatchesSnapshot(HTML5StringRenderer::render($result));
    }
}
