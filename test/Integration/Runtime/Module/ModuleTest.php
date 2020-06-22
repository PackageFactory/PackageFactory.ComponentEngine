<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Integration\Runtime\Module;

use PackageFactory\ComponentEngine\Loader\RootLoader;
use PackageFactory\ComponentEngine\Parser\Ast\Module\Module;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Pragma\AfxVirtualDOMPragma;
use PackageFactory\ComponentEngine\Runtime\Context;
use PackageFactory\ComponentEngine\Test\Integration\BaseTestCase;

final class ModuleTest extends BaseTestCase
{
    /**
     * @return iterable<string, array<int, string>>
     */
    public function provider(): iterable
    {
        yield from $this->fixtures('example');
    }

    /**
     * @test
     * @dataProvider provider
     * @param string $filename
     * @return void
     */
    public function test(string $filename): void
    {
        $source = Source::createFromFile($filename);
        $tokenizer = Tokenizer::createFromSource($source);
        $stream = TokenStream::createFromTokenizer($tokenizer);

        $ast = Module::createFromTokenStream($stream);
        $result = $ast->evaluate(
            RootLoader::createFromConfiguration([]),
            AfxVirtualDOMPragma::create(),
            Context::createFromArray([
                'props' => [
                    'type' => 'button',
                    'isHighlighted' => true,
                    'title' => 'Hello World!',
                    'links' => [
                        ['href' => '#', 'label' => 'Foo', 'isVisible' => true]
                    ]
                ],
                'styles' => [
                    'button' => 'button',
                    'disabled' => 'disabled'
                ]
            ])
        );

        $this->assertMatchesSnapshot((string) $result);
    }
}
