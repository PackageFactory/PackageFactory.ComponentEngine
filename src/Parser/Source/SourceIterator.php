<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Source;

/**
 * @implements \Iterator<mixed, Fragment>
 */
final class SourceIterator implements \Iterator
{
    /**
     * @var Source
     */
    private $source;

    /**
     * @var \Iterator<Fragment>
     */
    private $iterator;

    /**
     * @var array|Fragment[]
     */
    private $lookAheadBuffer = [];

    private function __construct(Source $source) 
    {
        $this->source = $source;
        $this->rewind();
    }

    public static function createFromSource(Source $source): self
    {
        return new self($source);
    }

    public function lookAhead(int $length): ?Fragment
    {
        $count = count($this->lookAheadBuffer);

        if ($count > $length)  {
            return $this->lookAheadBuffer[$length - 1];
        }

        $iterator = $this->iterator;
        $fragment = null;

        for ($i = 0; $i < $length - $count; $i++) {
            if (!$iterator->valid()) {
                return null;
            }

            $this->lookAheadBuffer[] = $iterator->current();

            if ($fragment === null) {
                $fragment = $iterator->current();
            }
            else {
                $fragment = $fragment->append($iterator->current());
            }

            $iterator->next();
        }

        return $fragment;
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
    }

    /**
     * @return void
     */
    public function rewind()
    {
        $this->iterator = $this->source->getIterator();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->iterator->valid();
    }
}