<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer;

use PackageFactory\ComponentEngine\Parser\Source\Source;

/**
 * @implements \IteratorAggregate<mixed, Line>
 */
final class LineIterator implements \IteratorAggregate
{
    /**
     * @var Source
     */
    private $source;

    /**
     * @param Source $source
     */
    private function __construct(Source $source)
    {
        $this->source = $source;
    }

    /**
     * @param Source $source
     * @return self
     */
    public static function fromSource(Source $source): self
    {
        return new self($source);
    }

    /**
     * @return \Iterator<mixed, Line>
     */
    public function getIterator()
    {
        $tokenizer = Tokenizer::fromSource($this->source);
        $stream = TokenStream::fromTokenizer($tokenizer);

        $number = 1;
        while ($stream->valid()) {
            yield Line::fromTokenStream($number, $stream);
            $number++;
        }
    }
}