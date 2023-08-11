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

namespace PackageFactory\ComponentEngine\Language\Parser\Expression;

use PackageFactory\ComponentEngine\Language\AST\Node\BinaryOperation\BinaryOperator;
use PackageFactory\ComponentEngine\Language\Lexer\Rule\Rule;

enum Precedence: int
{
    //
    // Precedence indices as per https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Operators/Operator_Precedence
    //

    case ACCESS = 18;
    case UNARY = 15;
    case COMPARISON = 10;
    case EQUALITY = 9;
    case LOGICAL_AND = 5;
    case LOGICAL_OR = 4;
    case TERNARY = 3;
    case SEQUENCE = 1;

    public static function forRule(Rule $tokenType): self
    {
        return match ($tokenType) {
            Rule::BRACKET_ROUND_OPEN,
            Rule::BRACKET_ROUND_CLOSE,
            Rule::BRACKET_SQUARE_OPEN,
            Rule::BRACKET_SQUARE_CLOSE,
            Rule::SYMBOL_OPTCHAIN,
            Rule::SYMBOL_PERIOD => self::ACCESS,

            Rule::SYMBOL_EXCLAMATIONMARK => self::UNARY,

            Rule::SYMBOL_GREATER_THAN,
            Rule::SYMBOL_GREATER_THAN_OR_EQUAL,
            Rule::SYMBOL_LESS_THAN,
            Rule::SYMBOL_LESS_THAN_OR_EQUAL => self::COMPARISON,

            Rule::SYMBOL_STRICT_EQUALS,
            Rule::SYMBOL_NOT_EQUALS => self::EQUALITY,

            Rule::SYMBOL_BOOLEAN_AND => self::LOGICAL_AND,

            Rule::SYMBOL_NULLISH_COALESCE,
            Rule::SYMBOL_BOOLEAN_OR => self::LOGICAL_OR,

            Rule::SYMBOL_QUESTIONMARK,
            Rule::SYMBOL_COLON => self::TERNARY,

            default => self::SEQUENCE
        };
    }

    public static function forBinaryOperator(BinaryOperator $binaryOperator): self
    {
        return match ($binaryOperator) {
            BinaryOperator::AND => self::LOGICAL_AND,

            BinaryOperator::NULLISH_COALESCE,
            BinaryOperator::OR  => self::LOGICAL_OR,

            BinaryOperator::EQUAL,
            BinaryOperator::NOT_EQUAL => self::EQUALITY,

            BinaryOperator::GREATER_THAN,
            BinaryOperator::GREATER_THAN_OR_EQUAL,
            BinaryOperator::LESS_THAN,
            BinaryOperator::LESS_THAN_OR_EQUAL => self::COMPARISON
        };
    }

    public function mustStopAt(Rule $tokenType): bool
    {
        return self::forRule($tokenType)->value <= $this->value;
    }
}
