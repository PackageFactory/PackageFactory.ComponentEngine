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

namespace PackageFactory\ComponentEngine\Debug\Cli;

use PackageFactory\ComponentEngine\Parser\Lexer\LineIterator;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Source\Source;

final class SourcePrinter
{
    private function __construct(
        private readonly Source $source,
        private readonly ?Token $token,
        private readonly ?int $fromRow,
        private readonly ?int $toRow
    ) {
    }

    public static function fromSource(Source $source): self
    {
        return new self($source, null, null, null);
    }

    public static function fromToken(Token $token): self
    {
        return new self(
            source: $token->source,
            token: $token,
            fromRow: $token->start->rowIndex - 2,
            toRow: $token->start->rowIndex + 2
        );
    }

    public function withFromRow(int $fromRow): self
    {
        return new self(
            source: $this->source,
            token: $this->token,
            fromRow: $fromRow,
            toRow: $this->toRow
        );
    }

    public function withToRow(int $toRow): self
    {
        return new self(
            source: $this->source,
            token: $this->token,
            fromRow: $this->fromRow,
            toRow: $toRow
        );
    }

    public function print(): void
    {
        foreach (LineIterator::fromSource($this->source) as $line) {
            if (
                ($this->fromRow === null || $line->getNumber() - 1 >= $this->fromRow)
                && ($this->toRow === null || $line->getNumber() - 1 <= $this->toRow)
            ) {
                print(str_pad((string) $line->getNumber(), 4, ' ', STR_PAD_LEFT) . ' | ');

                foreach ($line as $token) {
                    if ($this->token !== null && $this->token->equals($token)) {
                        print("\033[91m" . $token->value . "\033[0m");
                    } else {
                        print($token->value);
                    }
                }

                print(PHP_EOL);
            }
        }
    }
}
