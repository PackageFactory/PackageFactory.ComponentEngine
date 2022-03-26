<?php

/**
 * PackageFactory.ComponentEngine - Universal View Components for PHP
 *   Copyright (C) 2022 Contributors of PackageFactory.ComponentEngine
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace PackageFactory\ComponentEngine\Parser\Source;

/**
 * @implements \Iterator<mixed, Fragment>
 */
final class SourceIterator implements \Iterator
{
    /**
     * @var \Iterator<Fragment>
     */
    private \Iterator $iterator;

    /**
     * @var array|Fragment[]
     */
    private array $lookAheadBuffer = [];

    private function __construct(private readonly Source $source)
    {
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
            if ($lookAhead->value === $characterSequence) {
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
