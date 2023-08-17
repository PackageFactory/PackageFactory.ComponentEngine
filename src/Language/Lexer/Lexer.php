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
use PackageFactory\ComponentEngine\Language\Lexer\Rule\RuleInterface;
use PackageFactory\ComponentEngine\Language\Lexer\Rule\Rules;
use PackageFactory\ComponentEngine\Language\Lexer\Scanner\Scanner;
use PackageFactory\ComponentEngine\Language\Lexer\Scanner\ScannerException;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Range;

final class Lexer
{
    private static Rules $RULES_SPACE;
    private static Rules $RULES_SPACE_AND_COMMENTS;

    private readonly Scanner $scanner;

    public readonly Buffer $buffer;

    public function __construct(string $source)
    {
        self::$RULES_SPACE = Rules::from(
            Rule::SPACE,
            Rule::END_OF_LINE
        );
        self::$RULES_SPACE_AND_COMMENTS = Rules::from(
            Rule::SPACE,
            Rule::END_OF_LINE,
            Rule::COMMENT
        );

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

    public function read(Rule $rule): void
    {
        if ($this->scanner->scan($rule)) {
            $this->scanner->commit();
            return;
        }

        if ($this->scanner->isEnd()) {
            throw LexerException::becauseOfUnexpectedEndOfSource(
                expectedRules: Rules::from($rule),
                affectedRangeInSource: $this->scanner->getBuffer()->getRange()
            );
        }

        throw LexerException::becauseOfUnexpectedCharacterSequence(
            expectedRules: Rules::from($rule),
            affectedRangeInSource: $this->scanner->getBuffer()->getRange(),
            actualCharacterSequence: $this->scanner->getBuffer()->getContents()
        );
    }

    /** @phpstan-impure */
    public function readOneOf(Rules $rules): Rule
    {
        if ($rule = $this->scanner->scanOneOf(...$rules->items)) {
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

    public function probe(Rule $rule): bool
    {
        if ($this->scanner->scan($rule)) {
            $this->scanner->commit();
            return true;
        }

        $this->scanner->dismiss();
        return false;
    }

    /** @phpstan-impure */
    public function probeOneOf(Rules $rules): ?Rule
    {
        if ($rule = $this->scanner->scanOneOf(...$rules->items)) {
            $this->scanner->commit();
            assert($rule instanceof Rule);
            return $rule;
        }

        $this->scanner->dismiss();
        return null;
    }

    public function peek(Rule $rule): bool
    {
        $result = $this->scanner->scan($rule);
        $this->scanner->dismiss();

        return $result;
    }

    /** @phpstan-impure */
    public function peekOneOf(Rules $rules): ?Rule
    {
        $rule = $this->scanner->scanOneOf(...$rules->items);
        $this->scanner->dismiss();

        assert($rule === null || $rule instanceof Rule);
        return $rule;
    }

    public function expect(Rule $rule): void
    {
        if ($this->scanner->isEnd()) {
            throw LexerException::becauseOfUnexpectedEndOfSource(
                expectedRules: Rules::from($rule),
                affectedRangeInSource: $this->scanner->getBuffer()->getRange()
            );
        }

        if (!$this->scanner->scan($rule)) {
            throw LexerException::becauseOfUnexpectedCharacterSequence(
                expectedRules: Rules::from($rule),
                affectedRangeInSource: $this->scanner->getBuffer()->getRange(),
                actualCharacterSequence: $this->scanner->getBuffer()->getContents()
            );
        }

        $this->scanner->dismiss();
    }

    /** @phpstan-impure */
    public function expectOneOf(Rules $rules): Rule
    {
        if ($this->scanner->isEnd()) {
            throw LexerException::becauseOfUnexpectedEndOfSource(
                expectedRules: $rules,
                affectedRangeInSource: $this->scanner->getBuffer()->getRange()
            );
        }

        if ($rule = $this->scanner->scanOneOf(...$rules->items)) {
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
        while ($this->scanner->scanOneOf(...self::$RULES_SPACE->items)) {
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
        while ($this->scanner->scanOneOf(...self::$RULES_SPACE_AND_COMMENTS->items)) {
            $this->scanner->commit();
        }

        if ($this->scanner->isEnd()) {
            $this->scanner->commit();
        } else {
            $this->scanner->dismiss();
        }
    }
}
