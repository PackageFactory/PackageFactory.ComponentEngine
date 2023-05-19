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

namespace PackageFactory\ComponentEngine\Definition;

use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;
use Parsica\Parsica\Parser;

use function Parsica\Parsica\any;
use function Parsica\Parsica\fail;
use function Parsica\Parsica\lookAhead;
use function Parsica\Parsica\pure;
use function Parsica\Parsica\string;
use function Parsica\Parsica\succeed;

enum Precedence: int
{
    //
    // Precedence indices as per https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Operators/Operator_Precedence
    //

    case ACCESS = 18;
    case UNARY = 15;
    case POINT = 13;
    case DASH = 12;
    case COMPARISON = 10;
    case EQUALITY = 9;
    case LOGICAL_AND = 5;
    case LOGICAL_OR = 4;
    case TERNARY = 3;
    case SEQUENCE = 1;

    private const ARRAY_OF_STRINGS_TO_PRECEDENCE = [
        [['(', ')', '[', ']', '?.', '.'], self::ACCESS],
        [['!'], self::UNARY],
        [['*', '/', '%'], self::POINT],
        [['+', '-'], self::DASH],
        [['+', '-'], self::DASH],
        [['>', '>=', '<', '<='], self::COMPARISON],
        [['===', '!=='], self::EQUALITY],
        [['&&'], self::LOGICAL_AND],
        [['||'], self::LOGICAL_OR],
        [['?', ':'], self::TERNARY]
    ];

    public static function forTokenType(TokenType $tokenType): self
    {
        return match ($tokenType) {
            TokenType::BRACKET_ROUND_OPEN,
            TokenType::BRACKET_ROUND_CLOSE,
            TokenType::BRACKET_SQUARE_OPEN,
            TokenType::BRACKET_SQUARE_CLOSE,
            TokenType::OPTCHAIN,
            TokenType::PERIOD => self::ACCESS,

            TokenType::OPERATOR_BOOLEAN_NOT => self::UNARY,

            TokenType::OPERATOR_ARITHMETIC_MULTIPLY_BY,
            TokenType::OPERATOR_ARITHMETIC_DIVIDE_BY,
            TokenType::OPERATOR_ARITHMETIC_MODULO => self::POINT,

            TokenType::OPERATOR_ARITHMETIC_PLUS,
            TokenType::OPERATOR_ARITHMETIC_MINUS => self::DASH,

            TokenType::COMPARATOR_GREATER_THAN,
            TokenType::COMPARATOR_GREATER_THAN_OR_EQUAL,
            TokenType::COMPARATOR_LESS_THAN,
            TokenType::COMPARATOR_LESS_THAN_OR_EQUAL => self::COMPARISON,

            TokenType::COMPARATOR_EQUAL,
            TokenType::COMPARATOR_NOT_EQUAL => self::EQUALITY,

            TokenType::OPERATOR_BOOLEAN_AND => self::LOGICAL_AND,

            TokenType::OPERATOR_BOOLEAN_OR => self::LOGICAL_OR,

            TokenType::QUESTIONMARK,
            TokenType::COLON => self::TERNARY,

            default => self::SEQUENCE
        };
    }

    private static function getPrecedenceMatcher(): Parser
    {
        static $matcher;
        if ($matcher) {
            return $matcher;
        }
        $parsers = [];
        foreach (self::ARRAY_OF_STRINGS_TO_PRECEDENCE as [$strings, $precedence]) {
            $toPrecedence = fn () => $precedence;
            foreach ($strings as $string) {
                $parsers[] = string($string)->map($toPrecedence);
            }
        }
        $matcher = any(...$parsers, ...[pure(self::SEQUENCE)]);
        return $matcher;
    }

    public function check(): Parser
    {
        return lookAhead(self::getPrecedenceMatcher()->bind(function (Precedence $precedence) {
            if ($this->mustStopAt($precedence)) {
                return fail('<stopped at precedence>');
            }
            return succeed();
        }))->label('delegate in Precedence(' . $this->name . ')');
    }

    public function mustStopAt(self $precedence): bool
    {
        return $precedence->value <= $this->value;
    }

    public function mustStopAtTokenType(TokenType $tokenType): bool
    {
        return $this->mustStopAt(self::forTokenType($tokenType));
    }
}
