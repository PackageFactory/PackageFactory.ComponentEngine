<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer;

use PackageFactory\ComponentEngine\Parser\Source\Source;

/**
 * @implements \Iterator<mixed, Token>
 */
final class TokenStream implements \Iterator
{
    /**
     * @var Tokenizer
     */
    private $tokenizer;

    /**
     * @var \Iterator<Token>
     */
    private $iterator;

    /**
     * @var array|Token[]
     */
    private $lookAheadBuffer = [];

    /**
     * @var Token
     */
    private $last;

    private function __construct(Tokenizer $tokenizer) 
    {
        $this->tokenizer = $tokenizer;
        $this->rewind();
    }

    public static function fromTokenizer(Tokenizer $tokenizer): self
    {
        return new self($tokenizer);
    }

    public function getLast(): Token
    {
        return $this->last;
    }

    public function lookAhead(int $length): ?Token
    {
        $count = count($this->lookAheadBuffer);

        if ($count > $length)  {
            return $this->lookAheadBuffer[$length - 1];
        }

        $iterator = $this->iterator;
        $token = null;

        for ($i = 0; $i < $length - $count; $i++) {
            if (!$iterator->valid()) {
                return null;
            }

            $token = $iterator->current();
            $this->lookAheadBuffer[] = $token;
            $iterator->next();
        }

        return $token;
    }

    public function skip(int $length): void
    {
        for ($i = 0; $i < $length; $i++) {
            $this->next();
        }
    }

    /**
     * @return Token
     */
    public function current()
    {
        if ($this->lookAheadBuffer) {
            return $this->lookAheadBuffer[0];
        } else {
            return $this->iterator->current();
        }
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return $this->iterator->key();
    }

    /**
     * @return void
     */
    public function next()
    {
        if ($this->lookAheadBuffer) {
            array_shift($this->lookAheadBuffer);
        }
        else {
            $this->iterator->next();
        }

        $this->last = $this->iterator->current();
    }

    /**
     * @return void
     */
    public function rewind()
    {
        $this->iterator = $this->tokenizer->getIterator();
        $this->last = $this->iterator->current();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->iterator->valid();
    }
}