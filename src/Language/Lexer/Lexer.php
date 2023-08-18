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

namespace PackageFactory\ComponentEngine\Language\Lexer;

use PackageFactory\ComponentEngine\Language\Lexer\Buffer\Buffer;
use PackageFactory\ComponentEngine\Language\Lexer\Rule\Rule;
use PackageFactory\ComponentEngine\Language\Lexer\Scanner\Scanner;
use PackageFactory\ComponentEngine\Language\Lexer\Scanner\ScannerException;

final class Lexer
{
    private const RULES_SPACE = [
        Rule::SPACE,
        Rule::END_OF_LINE
    ];
    private const RULES_SPACE_AND_COMMENTS = [
        Rule::SPACE,
        Rule::END_OF_LINE,
        Rule::COMMENT
    ];

    private readonly Scanner $scanner;

    public readonly Buffer $buffer;

    public function __construct(string $source)
    {
        $this->scanner = new Scanner($source);
        $this->buffer = $this->scanner->getBuffer();
    }

    public function isEnd(): bool
    {
        return $this->scanner->isEnd();
    }

    public function assertIsEnd(): void
    {
        try {
            $this->scanner->assertIsEnd();
        } catch (ScannerException $e) {
            throw LexerException::becauseOfScannerException($e);
        }
    }

    /** @phpstan-impure */
    public function read(Rule ...$rules): Rule
    {
        if ($rule = $this->scanner->scan(...$rules)) {
            $this->scanner->commit();
            assert($rule instanceof Rule);
            return $rule;
        }

        if ($this->scanner->isEnd()) {
            throw LexerException::becauseOfUnexpectedEndOfSource(
                expectedRules: $rules,
                affectedRangeInSource: $this->scanner->getBuffer()->getRange()
            );
        }

        throw LexerException::becauseOfUnexpectedCharacterSequence(
            expectedRules: $rules,
            affectedRangeInSource: $this->scanner->getBuffer()->getRange(),
            actualCharacterSequence: $this->scanner->getBuffer()->getContents()
        );
    }

    /** @phpstan-impure */
    public function probe(Rule ...$rules): ?Rule
    {
        if ($rule = $this->scanner->scan(...$rules)) {
            $this->scanner->commit();
            assert($rule instanceof Rule);
            return $rule;
        }

        $this->scanner->dismiss();
        return null;
    }

    /** @phpstan-impure */
    public function peek(Rule ...$rules): ?Rule
    {
        $rule = $this->scanner->scan(...$rules);
        $this->scanner->dismiss();

        assert($rule === null || $rule instanceof Rule);
        return $rule;
    }

    /** @phpstan-impure */
    public function expect(Rule ...$rules): Rule
    {
        if ($this->scanner->isEnd()) {
            throw LexerException::becauseOfUnexpectedEndOfSource(
                expectedRules: $rules,
                affectedRangeInSource: $this->scanner->getBuffer()->getRange()
            );
        }

        if ($rule = $this->scanner->scan(...$rules)) {
            $this->scanner->dismiss();
            assert($rule instanceof Rule);
            return $rule;
        }

        throw LexerException::becauseOfUnexpectedCharacterSequence(
            expectedRules: $rules,
            affectedRangeInSource: $this->scanner->getBuffer()->getRange(),
            actualCharacterSequence: $this->scanner->getBuffer()->getContents()
        );
    }

    public function skipSpace(): void
    {
        while ($this->scanner->scan(...self::RULES_SPACE)) {
            $this->scanner->commit();
        }

        if ($this->scanner->isEnd()) {
            $this->scanner->commit();
        } else {
            $this->scanner->dismiss();
        }
    }

    public function skipSpaceAndComments(): void
    {
        while ($this->scanner->scan(...self::RULES_SPACE_AND_COMMENTS)) {
            $this->scanner->commit();
        }

        if ($this->scanner->isEnd()) {
            $this->scanner->commit();
        } else {
            $this->scanner->dismiss();
        }
    }
}
