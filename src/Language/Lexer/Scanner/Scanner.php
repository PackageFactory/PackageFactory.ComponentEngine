<?php

/**
 * PackageFactory.ComponentEngine - Universal View Components for PHP
 *   Copyright (C) 2023 Contributors of PackageFactory.ComponentEngine
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

namespace PackageFactory\ComponentEngine\Language\Lexer\Scanner;

use PackageFactory\ComponentEngine\Language\Lexer\Buffer\Buffer;
use PackageFactory\ComponentEngine\Language\Lexer\CharacterStream\CharacterStream;
use PackageFactory\ComponentEngine\Language\Lexer\Matcher\Result;
use PackageFactory\ComponentEngine\Language\Lexer\Rule\RuleInterface;

final class Scanner implements ScannerInterface
{
    private readonly ScannerState $main;
    private readonly ScannerState $branch;

    private bool $isHalted;
    private int $offset;

    public function __construct(string $source)
    {
        $this->main = new ScannerState(
            characterStream: new CharacterStream($source),
            buffer: new Buffer()
        );
        $this->branch = new ScannerState(
            characterStream: new CharacterStream($source),
            buffer: new Buffer()
        );

        $this->isHalted = false;
        $this->offset = 0;
    }

    public function getBuffer(): Buffer
    {
        return $this->branch->buffer;
    }

    public function isEnd(): bool
    {
        return $this->branch->characterStream->isEnd();
    }

    public function assertIsEnd(): void
    {
        if (!$this->branch->characterStream->isEnd()) {
            $this->branch->buffer->flush();
            $this->branch->buffer->append($this->branch->characterStream->current());
            $this->isHalted = true;

            throw ScannerException::becauseOfUnexpectedExceedingSource(
                affectedRangeInSource: $this->branch->buffer->getRange(),
                exceedingCharacter: $this->branch->buffer->getContents()
            );
        }
    }

    public function scan(RuleInterface $rule): bool
    {
        assert(!$this->isHalted);

        $this->branch->buffer->flush();
        $this->offset = 0;

        $matcher = $rule->getMatcher();
        while (true) {
            $character = $this->branch->characterStream->current();
            $result = $matcher->match($character, $this->offset);

            if ($result === Result::SATISFIED) {
                $this->isHalted = true;
                return true;
            }

            if ($result === Result::CANCEL) {
                $this->branch->buffer->append($character);
                $this->isHalted = true;
                return false;
            }

            $this->offset++;
            $this->branch->buffer->append($character);
            $this->branch->characterStream->next();
        }
    }

    public function scanOneOf(RuleInterface ...$rules): ?RuleInterface
    {
        assert(!$this->isHalted);

        $this->branch->buffer->flush();
        $this->offset = 0;

        $candidates = $rules;
        while ($candidates) {
            $character = $this->branch->characterStream->current();

            $nextCandidates = [];
            foreach ($candidates as $candidate) {
                $matcher = $candidate->getMatcher();
                $result = $matcher->match($character, $this->offset);

                if ($result === Result::SATISFIED) {
                    $this->isHalted = true;
                    return $candidate;
                }

                if ($result === Result::KEEP) {
                    $nextCandidates[] = $candidate;
                }
            }

            if ($candidates = $nextCandidates) {
                $this->offset++;
                $this->branch->characterStream->next();
            }

            $this->branch->buffer->append($character);
        }

        $this->isHalted = true;
        return null;
    }

    public function commit(): void
    {
        $this->branch->overwrite($this->main);
        $this->isHalted = false;
    }

    public function dismiss(): void
    {
        $this->main->overwrite($this->branch);
        $this->isHalted = false;
    }
}
