<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer;

use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

/**
 * @implements \IteratorAggregate<mixed, Token>
 */
final class Tokenizer implements \IteratorAggregate
{
    /**
     * @var Source
     */
    private $source;

    /**
     * @var class-string<
     *  Scope\Afx::class |
     *  Scope\Comment::class |
     *  Scope\Expression::class |
     *  Scope\Identifier::class |
     *  Scope\Keyword::class |
     *  Scope\Module::class |
     *  Scope\Number::class |
     *  Scope\StringLiteral::class |
     *  Scope\TemplateLiteral::class |
     *  Scope\Whitespace::class
     * >
     */
    private $rootScope = Scope\Module::class;

    /**
     * @param Source $source
     * @param class-string<
     *  Scope\Afx::class |
     *  Scope\Comment::class |
     *  Scope\Expression::class |
     *  Scope\Identifier::class |
     *  Scope\Keyword::class |
     *  Scope\Module::class |
     *  Scope\Number::class |
     *  Scope\StringLiteral::class |
     *  Scope\TemplateLiteral::class |
     *  Scope\Whitespace::class
     * > $rootScope
     */
    private function __construct(Source $source, string $rootScope)
    {
        $this->source = $source;
        $this->rootScope = $rootScope;
    }

    /**
     * @param Source $source
     * @param class-string<
     *  Scope\Afx::class |
     *  Scope\Comment::class |
     *  Scope\Expression::class |
     *  Scope\Identifier::class |
     *  Scope\Keyword::class |
     *  Scope\Module::class |
     *  Scope\Number::class |
     *  Scope\StringLiteral::class |
     *  Scope\TemplateLiteral::class |
     *  Scope\Whitespace::class
     * > $rootScope
     * @return Tokenizer
     */
    public static function createFromSource(
        Source $source,
        string $rootScope = Scope\Module::class
    ): Tokenizer {
        return new Tokenizer($source, $rootScope);
    }

    /**
     * @return Source
     */
    public function getSource(): Source
    {
        return $this->source;
    }

    /**
     * @return \Iterator<mixed, Token>
     */
    public function getIterator(): \Iterator
    {
        $sourceIterator = SourceIterator::createFromSource($this->source);
        yield from $this->rootScope::tokenize($sourceIterator);
    }
}