<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Loader;

use PackageFactory\ComponentEngine\Parser\Ast\Module\Module;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Source\Path;
use PackageFactory\ComponentEngine\Parser\Source\Source;

final class ComponentLoader implements LoaderInterface
{
    public function load(Path $path): Module
    {
        $source = Source::fromFile((string) $path);
        $tokenizer = Tokenizer::fromSource($source);
        $stream = TokenStream::fromTokenizer($tokenizer);

        return Module::fromTokenStream($stream);
    }
}