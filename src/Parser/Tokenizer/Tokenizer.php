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

namespace PackageFactory\ComponentEngine\Parser\Tokenizer;

use PackageFactory\ComponentEngine\Parser\Source\Fragment;
use PackageFactory\ComponentEngine\Parser\Source\Source;

/**
 * @implements \IteratorAggregate<mixed, Token>
 */
final class Tokenizer implements \IteratorAggregate
{
    private function __construct(private readonly Source $source)
    {
    }

    public static function fromSource(Source $source): Tokenizer
    {
        return new Tokenizer(source: $source);
    }

    /**
     * @return \Iterator<mixed,Token>
     */
    public function getIterator(): \Iterator
    {
        yield from self::block($this->source->getIterator());
    }

    /**
     * @param \Iterator<mixed, Fragment> $fragments
     * @return \Iterator<mixed, Token>
     */
    private static function block(\Iterator $fragments): \Iterator
    {
        $bracket = TokenType::tryBracketOpenFromFragment($fragments->current());
        $buffer = Buffer::empty();

        if ($bracket) {
            yield from $buffer->append($fragments->current())->flush($bracket);
            $fragments->next();
        }

        while ($fragments->valid()) {
            /** @var Fragment $fragment */
            $fragment = $fragments->current();

            if ($bracket) {
                $closingBracket = $bracket->closingBracket();

                if ($closingBracket->matchesString($fragment->value)) {
                    yield from self::flushRemainder($buffer);
                    yield from $buffer->append($fragments->current())->flush($closingBracket);
                    $fragments->next();
                    return;
                }
            }

            $delegate = match (CharacterType::get($fragment->value)) {
                CharacterType::COMMENT_DELIMITER => self::comment($fragments),
                CharacterType::STRING_DELIMITER => self::string($fragments),
                CharacterType::TEMPLATE_LITERAL_DELIMITER => self::templateLiteral($fragments),
                CharacterType::BRACKET_OPEN => self::block($fragments),
                CharacterType::ANGLE_OPEN => self::angle($fragments),
                CharacterType::PERIOD => match (TokenType::fromBuffer($buffer)) {
                    TokenType::NUMBER_BINARY,
                    TokenType::NUMBER_OCTAL,
                    TokenType::NUMBER_DECIMAL,
                    TokenType::NUMBER_HEXADECIMAL => null,
                    default => self::period($fragments)
                },
                CharacterType::ANGLE_CLOSE,
                CharacterType::FORWARD_SLASH,
                CharacterType::SYMBOL => self::symbol($fragments),
                CharacterType::SPACE => self::space($fragments),
                default => null
            };

            if ($delegate) {
                yield from self::flushRemainder($buffer);
                yield from $delegate;
            } else {
                $buffer->append($fragment);
                $fragments->next();
            }
        }

        yield from self::flushRemainder($buffer);
    }

    /**
     * @param Buffer $buffer
     * @return \Iterator<mixed, Token>
     */
    private static function flushRemainder(Buffer $buffer): \Iterator
    {
        yield from $buffer->flush(TokenType::fromBuffer($buffer));
    }

    /**
     * @param \Iterator<mixed, Fragment> $fragments
     * @return \Iterator<mixed, Token>
     */
    private static function string(\Iterator $fragments): \Iterator
    {
        $delimiter = $fragments->current()->value;
        $fragments->next();

        $buffer = Buffer::empty();

        while ($fragments->valid()) {
            switch ($fragments->current()->value) {
                case $delimiter:
                    yield from $buffer->flush(TokenType::STRING_QUOTED);
                    $fragments->next();
                    return;

                case '\\':
                    $buffer->append($fragments->current());
                    $fragments->next();

                    if (!$fragments->valid()) {
                        throw new \Exception("@TODO: Unexpected end of input");
                    }

                    $buffer->append($fragments->current());
                    $fragments->next();
                    break;

                default:
                    $buffer->append($fragments->current());
                    $fragments->next();
                    break;
            }
        }
    }

    /**
     * @param \Iterator<mixed, Fragment> $fragments
     * @return \Iterator<mixed, Token>
     */
    public static function templateLiteral(\Iterator $fragments): \Iterator
    {
        $buffer = Buffer::empty();
        $buffer->append($fragments->current());

        yield from $buffer->flush(TokenType::TEMPLATE_LITERAL_START);

        $fragments->next();

        while ($fragments->valid()) {

            switch ($fragments->current()->value) {
                case '`':
                    yield from $buffer->flush(TokenType::STRING_QUOTED);
                    $buffer->append($fragments->current());
                    yield from $buffer->flush(TokenType::TEMPLATE_LITERAL_END);
                    $fragments->next();
                    return;

                case '$':
                    $dollarSignBuffer = Buffer::empty()->append($fragments->current());
                    $fragments->next();

                    if (!$fragments->valid()) {
                        throw new \Exception("@TODO: Unexpected end of input");
                    }
                    
                    $nextFragment = $fragments->current();

                    if ($nextFragment->value === '{') {
                        yield from $buffer->flush(TokenType::STRING_QUOTED);
                        yield from $dollarSignBuffer->flush(TokenType::DOLLAR);
                        yield from self::block($fragments);
                    }
                    break;

                case '\\':
                    $buffer->append($fragments->current());
                    $fragments->next();

                    if (!$fragments->valid()) {
                        throw new \Exception("@TODO: Unexpected end of input");
                    }

                    $buffer->append($fragments->current());
                    $fragments->next();
                    break;

                default:
                    $buffer->append($fragments->current());
                    $fragments->next();
                    break;
            }
        }
    }

    /**
     * @param \Iterator<mixed, Fragment> $fragments
     * @return \Iterator<mixed, Token>
     */
    public static function period(\Iterator $fragments): \Iterator
    {
        $buffer = Buffer::empty()->append($fragments->current());
        $fragments->next();

        if ($fragments->valid()) {
            $fragment = $fragments->current();

            if (CharacterType::DIGIT->is($fragment->value)) {
                $buffer->append($fragment);
                $fragments->next();

                while ($fragments->valid()) {
                    $fragment = $fragments->current();

                    if (CharacterType::DIGIT->is($fragment->value)) {
                        $buffer->append($fragment);
                        $fragments->next();
                    } else {
                        yield from $buffer->flush(TokenType::NUMBER_DECIMAL);
                        return;
                    }
                }
            }
        }

        yield from $buffer->flush(TokenType::PERIOD);
    }

    public static function symbol(\Iterator $fragments, ?Buffer $buffer = null): \Iterator
    {
        $buffer ??= Buffer::empty();
        $capture = true;

        while ($capture && $fragments->valid()) {
            /** @var Fragment $fragment */
            $fragment = $fragments->current();
            $capture = match (CharacterType::get($fragment->value)) {
                CharacterType::ANGLE_CLOSE,
                CharacterType::FORWARD_SLASH,
                CharacterType::PERIOD,
                CharacterType::SYMBOL => $buffer->append($fragment) && true,
                default => false
            };

            if ($capture) $fragments->next();
        }

        yield from match ($buffer->value()) {
            '+' => $buffer->flush(TokenType::OPERATOR_ARITHMETIC_PLUS),
            '-' => $buffer->flush(TokenType::OPERATOR_ARITHMETIC_MINUS),
            '*' => $buffer->flush(TokenType::OPERATOR_ARITHMETIC_MULTIPLY_BY),
            '/' => $buffer->flush(TokenType::OPERATOR_ARITHMETIC_DIVIDE_BY),
            '%' => $buffer->flush(TokenType::OPERATOR_ARITHMETIC_MODULO),
            '&&' => $buffer->flush(TokenType::OPERATOR_BOOLEAN_AND),
            '||' => $buffer->flush(TokenType::OPERATOR_BOOLEAN_OR),
            '!' => $buffer->flush(TokenType::OPERATOR_BOOLEAN_NOT),
            '>' => $buffer->flush(TokenType::COMPARATOR_GREATER_THAN),
            '>=' => $buffer->flush(TokenType::COMPARATOR_GREATER_THAN_OR_EQUAL),
            '<' => $buffer->flush(TokenType::COMPARATOR_LESS_THAN),
            '<=' => $buffer->flush(TokenType::COMPARATOR_LESS_THAN_OR_EQUAL),
            '===' => $buffer->flush(TokenType::COMPARATOR_EQUAL),
            '!==' => $buffer->flush(TokenType::COMPARATOR_NOT_EQUAL),
            '->' => $buffer->flush(TokenType::ARROW_SINGLE),
            ':' => $buffer->flush(TokenType::COLON),
            '.' => $buffer->flush(TokenType::PERIOD),
            ',' => $buffer->flush(TokenType::COMMA),
            '=' => $buffer->flush(TokenType::EQUALS),
            '?' => $buffer->flush(TokenType::QUESTIONMARK),
            '$' => $buffer->flush(TokenType::DOLLAR),
            default => self::flushRemainder($buffer)
        };
    }

    /**
     * @param \Iterator<mixed, Fragment> $fragments
     * @return \Iterator<mixed, Token>
     */
    public static function angle(\Iterator $fragments): \Iterator
    {
        $buffer = Buffer::empty();

        /** @var Fragment $fragment */
        $fragment = $fragments->current();
        $buffer->append($fragment);

        $fragments->next();
        if ($fragments->valid()) {
            /** @var Fragment $fragment */
            $fragment = $fragments->current();
            yield from match (CharacterType::get($fragment->value)) {
                CharacterType::SYMBOL => self::symbol($fragments, $buffer),
                CharacterType::SPACE => $buffer->flush(TokenType::COMPARATOR_LESS_THAN),
                default => self::tag($fragments, $buffer)
            };
        }
    }

    /**
     * @param \Iterator<mixed, Fragment> $fragments
     * @param null|Buffer $buffer
     * @return \Iterator<mixed, Token>
     */
    public static function tag(\Iterator $fragments, ?Buffer $buffer = null): \Iterator
    {
        $buffer ??= Buffer::empty();
        $isClosing = false;

        while ($fragments->valid()) {
            /** @var Fragment $fragment */
            $fragment = $fragments->current();
            if ($buffer->value() === '<') {
                if ($fragment->value === '/') {
                    yield from $buffer->append($fragment)->flush(TokenType::TAG_START_CLOSING);
                    $fragments->next();
                    $isClosing = true;
                    continue;
                } else {
                    yield from $buffer->flush(TokenType::TAG_START_OPENING);
                }
            }

            switch (true) {
                case $fragment->value === '=':
                    yield from $buffer->flush(TokenType::STRING);
                    yield from $buffer->append($fragment)->flush(TokenType::EQUALS);
                    $fragments->next();
                    break;
                case $fragment->value === '{':
                    yield from $buffer->flush(TokenType::STRING);
                    yield from self::block($fragments);
                    break;
                case $fragment->value === '"':
                    yield from $buffer->flush(TokenType::STRING);
                    yield from self::string($fragments);
                    break;
                case $fragment->value === '/':
                    yield from $buffer->flush(TokenType::STRING);
                    $buffer->append($fragment);
                    $fragments->next();
                    if ($nextFragment = $fragments->current()) {
                        if ($nextFragment->value === '>') {
                            yield from $buffer->append($nextFragment)->flush(TokenType::TAG_SELF_CLOSE);
                            $fragments->next();
                        } else {
                            throw new \Exception("@TODO: Illegal Character");
                        }
                    }


                    return;
                case $fragment->value === '>':
                    yield from $buffer->flush(TokenType::STRING);
                    yield from $buffer->append($fragment)->flush(TokenType::TAG_END);
                    $fragments->next();

                    if ($isClosing) {
                        return;
                    } else {
                        $buffer = (yield from self::tagContent($fragments)) ?? Buffer::empty();
                    }
                    break;
                case ctype_space($fragment->value):
                    yield from $buffer->flush(TokenType::STRING);
                    yield from self::space($fragments);
                    break;
                default:
                    $buffer->append($fragment);
                    $fragments->next();
                    break;
            }
        }

        yield from $buffer->flush(TokenType::STRING);
    }

    /**
     * @param \Iterator<mixed, Fragment> $fragments
     * @return \Iterator<mixed, Token>
     */
    public static function tagContent(\Iterator $fragments): \Iterator
    {
        $buffer = Buffer::empty();
        while ($fragments->valid()) {
            /** @var Fragment $fragment */
            $fragment = $fragments->current();
            switch (true) {
                case $fragment->value === '{':
                    yield from $buffer->flush(TokenType::STRING);
                    yield from self::block($fragments);
                    break;
                case $fragment->value === '>':
                    throw new \Exception(sprintf('@TODO: Illegal Character "%s"', $fragment->value));
                case $fragment->value === '<':
                    $fragments->next();
                    if (!$fragments->valid()) {
                        throw new \Exception("@TODO: Unexpected end of input");
                    }
                    if ($fragments->current()->value === '/') {
                        yield from $buffer->flush(TokenType::STRING);
                        return Buffer::empty()->append($fragment);
                    }
                    yield from self::tag($fragments, Buffer::empty()->append($fragment));
                    break;
                case ctype_space($fragment->value):
                    yield from $buffer->flush(TokenType::STRING);
                    yield from self::space($fragments);
                    break;
                default:
                    $buffer->append($fragment);
                    $fragments->next();
                    break;
            }
        }

        yield from $buffer->flush(TokenType::STRING);
    }

    /**
     * @param \Iterator<mixed, Fragment> $fragments
     * @return \Iterator<mixed, Token>
     */
    public static function space(\Iterator $fragments): \Iterator
    {
        $buffer = Buffer::empty();

        while ($fragments->valid()) {
            /** @var Fragment $fragment */
            $fragment = $fragments->current();

            if ($fragment->value === PHP_EOL) {
                yield from $buffer->flush(TokenType::SPACE);
                yield from $buffer->append($fragment)->flush(TokenType::END_OF_LINE);
            } else if (ctype_space($fragment->value)) {
                $buffer->append($fragment);
            } else {
                break;
            }

            $fragments->next();
        }

        yield from $buffer->flush(TokenType::SPACE);
    }

    /**
     * @param \Iterator<mixed, Fragment> $fragments
     * @return \Iterator<mixed, Token>
     */
    public static function comment(\Iterator $fragments): \Iterator
    {
        $buffer = Buffer::empty();

        while ($fragments->valid()) {
            /** @var Fragment $fragment */
            $fragment = $fragments->current();

            if ($fragment->value === PHP_EOL) {
                break;
            }

            $buffer->append($fragment);
            $fragments->next();
        }

        yield from $buffer->flush(TokenType::COMMENT);
    }
}
