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
     * @param Source $source
     */
    private function __construct(Source $source)
    {
        $this->source = $source;
    }

    /**
     * @param Source $source
     * @return Tokenizer
     */
    public static function createFromSource(Source $source): Tokenizer
    {
        return new Tokenizer($source);
    }

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
        foreach (Tokenize\Root::tokenize($sourceIterator) as $token) {
            yield $token;
        }

        yield Token::create(
            TokenType::END_OF_FILE(),
            isset($token) ? $token->getValue() : '',
            isset($token) ? $token->getStart() : $this->source->getEnd(),
            isset($token) ? $token->getEnd() : $this->source->getEnd(),
            $this->source
        );
    }
}