<?php

declare(strict_types=1);

namespace PackageFactory\ComponentEngine\Parser\Lexer;

use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

/**
 * @implements \IteratorAggregate<mixed, Token>
 */
final class Tokenizer implements \IteratorAggregate
{
    private Source $source;

    /**
     * @var class-string<Scope\Afx|Scope\Comment|Scope\Expression|Scope\Identifier|Scope\Module|Scope\Number|Scope\StringLiteral|Scope\TemplateLiteral|Scope\Whitespace>
     */
    private $rootScope = Scope\Module::class;

    /**
     * @param Source $source
     * @param class-string<Scope\Afx|Scope\Comment|Scope\Expression|Scope\Identifier|Scope\Module|Scope\Number|Scope\StringLiteral|Scope\TemplateLiteral|Scope\Whitespace> $rootScope
     */
    private function __construct(Source $source, string $rootScope)
    {
        $this->source = $source;
        $this->rootScope = $rootScope;
    }

    /**
     * @param Source $source
     * @param class-string<Scope\Afx|Scope\Comment|Scope\Expression|Scope\Identifier|Scope\Module|Scope\Number|Scope\StringLiteral|Scope\TemplateLiteral|Scope\Whitespace> $rootScope
     * @return Tokenizer
     */
    public static function fromSource(
        Source $source,
        string $rootScope = Scope\Module::class
    ): Tokenizer {
        return new Tokenizer($source, $rootScope);
    }

    /**
     * @return \Iterator<mixed, Token>
     */
    public function getIterator(): \Iterator
    {
        $sourceIterator = SourceIterator::fromSource($this->source);
        yield from $this->rootScope::tokenize($sourceIterator);
    }
}
