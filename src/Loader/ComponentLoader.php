<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Loader;

use PackageFactory\ComponentEngine\Parser\Ast\Module\Module;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Source\Path;
use PackageFactory\ComponentEngine\Parser\Source\Source;

final class ComponentLoader implements LoaderInterface
{
    public function load(Path $path): Module
    {
        $source = Source::createFromFile((string) $path);
        $tokenizer = Tokenizer::createFromSource($source);
        $stream = TokenStream::createFromTokenizer($tokenizer);

        return Module::createFromTokenStream($stream);
    }
}