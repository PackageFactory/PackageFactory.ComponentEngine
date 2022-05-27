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

enum BinaryOperator: string
{
    case AND = 'AND';
    case OR = 'OR';

    case PLUS = 'PLUS';
    case MINUS = 'MINUS';
    case MULTIPLY_BY = 'MULTIPLY_BY';
    case DIVIDE_BY = 'DIVIDE_BY';
    case MODULO = 'MODULO';

    case EQUAL = 'EQUAL';
    case GREATER_THAN = 'GREATER_THAN';
    case GREATER_THAN_OR_EQUAL = 'GREATER_THAN_OR_EQUAL';
    case LESS_THAN = 'LESS_THAN';
    case LESS_THAN_OR_EQUAL = 'LESS_THAN_OR_EQUAL';

    public static function fromTokenType(TokenType $tokenType): self
    {
        return match ($tokenType) {
            TokenType::OPERATOR_BOOLEAN_AND => self::AND,
            TokenType::OPERATOR_BOOLEAN_OR => self::OR,

            TokenType::OPERATOR_ARITHMETIC_PLUS => self::PLUS,
            TokenType::OPERATOR_ARITHMETIC_MINUS => self::MINUS,
            TokenType::OPERATOR_ARITHMETIC_MULTIPLY_BY => self::MULTIPLY_BY,
            TokenType::OPERATOR_ARITHMETIC_DIVIDE_BY => self::DIVIDE_BY,
            TokenType::OPERATOR_ARITHMETIC_MODULO => self::MODULO,

            TokenType::COMPARATOR_EQUAL => self::EQUAL,
            TokenType::COMPARATOR_GREATER_THAN => self::GREATER_THAN,
            TokenType::COMPARATOR_GREATER_THAN_OR_EQUAL => self::GREATER_THAN_OR_EQUAL,
            TokenType::COMPARATOR_LESS_THAN => self::LESS_THAN,
            TokenType::COMPARATOR_LESS_THAN_OR_EQUAL => self::LESS_THAN_OR_EQUAL,

            default => throw new \Exception('@TODO: Unknown Binary Operator')
        };
    }

    public function toTokenType(): TokenType
    {
        return match ($this) {
            self::AND => TokenType::OPERATOR_BOOLEAN_AND,
            self::OR => TokenType::OPERATOR_BOOLEAN_OR,

            self::PLUS => TokenType::OPERATOR_ARITHMETIC_PLUS,
            self::MINUS => TokenType::OPERATOR_ARITHMETIC_MINUS,
            self::MULTIPLY_BY => TokenType::OPERATOR_ARITHMETIC_MULTIPLY_BY,
            self::DIVIDE_BY => TokenType::OPERATOR_ARITHMETIC_DIVIDE_BY,
            self::MODULO => TokenType::OPERATOR_ARITHMETIC_MODULO,

            self::EQUAL => TokenType::COMPARATOR_EQUAL,
            self::GREATER_THAN => TokenType::COMPARATOR_GREATER_THAN,
            self::GREATER_THAN_OR_EQUAL => TokenType::COMPARATOR_GREATER_THAN_OR_EQUAL,
            self::LESS_THAN => TokenType::COMPARATOR_LESS_THAN,
            self::LESS_THAN_OR_EQUAL => TokenType::COMPARATOR_LESS_THAN_OR_EQUAL
        };
    }

    public function toPrecedence(): Precedence
    {
        return match ($this) {
            self::AND => Precedence::LOGICAL_AND,

            self::OR => Precedence::LOGICAL_OR,

            self::PLUS,
            self::MINUS => Precedence::DASH,

            self::MULTIPLY_BY,
            self::DIVIDE_BY,
            self::MODULO => Precedence::POINT,

            self::EQUAL => Precedence::EQUALITY,

            self::GREATER_THAN,
            self::GREATER_THAN_OR_EQUAL,
            self::LESS_THAN,
            self::LESS_THAN_OR_EQUAL => Precedence::COMPARISON
        };
    }
}
