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

namespace PackageFactory\ComponentEngine\Parser\Lexer\Scope;

use PackageFactory\ComponentEngine\Parser\Lexer\Capture;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

final class Number
{
    const DIGITS_BIN = ['0', '1'];
    const DIGITS_OCT = ['0', '1', '2', '3', '4', '5', '6', '7'];
    const DIGITS_DEC = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    const DIGITS_HEX = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F'];

    public static function is(string $char): bool
    {
        return ctype_digit($char);
    }

    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenize(SourceIterator $iterator): \Iterator
    {
        if ($iterator->current()->value === '0') {
            if ($lookAhead = $iterator->lookAhead(2)) {
                $lookAhead = $lookAhead->value;
            } else {
                $lookAhead = '';
            }

            if ($lookAhead === '0b' || $lookAhead === '0B') {
                yield from self::tokenizeBinary($iterator);
            } elseif ($lookAhead === '0o') {
                yield from self::tokenizeOctal($iterator);
            } elseif ($lookAhead === '0x') {
                yield from self::tokenizeHexadecimal($iterator);
            } else {
                yield from self::tokenizeDecimal($iterator);
            }
        } else {
            yield from self::tokenizeDecimal($iterator);
        }
    }

    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenizeBinary(SourceIterator $iterator): \Iterator
    {
        $capture = Capture::fromFragment($iterator->current());
        $iterator->next();
        $capture->append($iterator->current());
        $iterator->next();

        while ($iterator->valid()) {
            $value = $iterator->current()->value;

            if (in_array($value, self::DIGITS_BIN)) {
                $capture->append($iterator->current());
            } else {
                break;
            }
            $iterator->next();
        }

        yield from $capture->flush(TokenType::NUMBER);
    }

    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenizeOctal(SourceIterator $iterator): \Iterator
    {
        $capture = Capture::fromFragment($iterator->current());
        $iterator->next();
        $capture->append($iterator->current());
        $iterator->next();

        while ($iterator->valid()) {
            $value = $iterator->current()->value;
            if (in_array($value, self::DIGITS_OCT)) {
                $capture->append($iterator->current());
            } else {
                break;
            }
            $iterator->next();
        }

        yield from $capture->flush(TokenType::NUMBER);
    }

    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenizeHexadecimal(SourceIterator $iterator): \Iterator
    {
        $capture = Capture::fromFragment($iterator->current());
        $iterator->next();
        $capture->append($iterator->current());
        $iterator->next();

        while ($iterator->valid()) {
            $value = $iterator->current()->value;
            if (in_array($value, self::DIGITS_HEX)) {
                $capture->append($iterator->current());
            } else {
                break;
            }
            $iterator->next();
        }

        yield from $capture->flush(TokenType::NUMBER);
    }

    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenizeDecimal(SourceIterator $iterator): \Iterator
    {
        $capture = Capture::createEmpty();
        $floatingPoint = false;
        $exponentiation = false;

        while ($iterator->valid()) {
            $value = $iterator->current()->value;
            if (in_array($value, self::DIGITS_DEC)) {
                $capture->append($iterator->current());
            } elseif ($value === '.' && !$floatingPoint && !$exponentiation) {
                $capture->append($iterator->current());
                $floatingPoint = true;
            } elseif (($value === 'e' || $value === 'E') && !$exponentiation) {
                $capture->append($iterator->current());
                $exponentiation = true;
            } else {
                break;
            }
            $iterator->next();
        }

        yield from $capture->flush(TokenType::NUMBER);
    }
}
