<?php

declare(strict_types=1);

namespace PackageFactory\ComponentEngine\Parser\Source;

/**
 * @implements \Iterator<mixed, Fragment>
 */
final class SourceIterator implements \Iterator
{
    private Source $source;

    /**
     * @var \Iterator<Fragment>
     */
    private \Iterator $iterator;

    /**
     * @var array|Fragment[]
     */
    private array $lookAheadBuffer = [];

    private function __construct(Source $source)
    {
        $this->source = $source;
        $this->rewind();
    }

    public static function fromSource(Source $source): self
    {
        return new self($source);
    }

    public function lookAhead(int $length): ?Fragment
    {
        $iterator = $this->iterator;
        $lookAhead = null;

        for ($i = 0; $i < $length; $i++) {
            if (isset($this->lookAheadBuffer[$i])) {
                $fragment = $this->lookAheadBuffer[$i];
            } elseif ($iterator->valid()) {
                $fragment = $iterator->current();
                $this->lookAheadBuffer[] = $fragment;
                $iterator->next();
            } else {
                return null;
            }

            if ($lookAhead === null) {
                $lookAhead = $fragment;
            } else {
                $lookAhead = $lookAhead->append($fragment);
            }
        }

        return $lookAhead;
    }

    public function willBe(string $characterSequence): ?Fragment
    {
        if ($lookAhead = $this->lookAhead(mb_strlen($characterSequence))) {
            if ($lookAhead->getValue() === $characterSequence) {
                return $lookAhead;
            }
        }

        return null;
    }

    public function skip(int $length): void
    {
        for ($i = 0; $i < $length; $i++) {
            $this->next();
        }
    }

    /**
     * @return Fragment
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

    public function next(): void
    {
        if ($this->lookAheadBuffer) {
            array_shift($this->lookAheadBuffer);
        } else {
            $this->iterator->next();
        }
    }

    public function rewind(): void
    {
        $this->iterator = $this->source->getIterator();
    }

    public function valid(): bool
    {
        return !empty($this->lookAheadBuffer) || $this->iterator->valid();
    }
}
