<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser;

use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Ast\Module;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;

final class Parser
{
    /**
     * @var Tokenizer
     */
    private $tokenizer;

    private function __construct(Tokenizer $tokenizer)
    {
        $this->tokenizer = $tokenizer;
    }

    public static function createFromTokenizer(Tokenizer $tokenizer): self
    {
        return new self($tokenizer);
    }

    public function parse(): Module
    {
        return Module::createFromTokenStream(
            TokenStream::createFromTokenizer(
                $this->tokenizer
            )
        );
    }
}