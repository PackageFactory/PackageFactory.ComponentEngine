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
use PackageFactory\ComponentEngine\Language\Lexer\Token\TokenType;

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

    public static function forTokenType(TokenType $tokenType): self
    {
        return match ($tokenType) {
            TokenType::BRACKET_ROUND_OPEN,
            TokenType::BRACKET_ROUND_CLOSE,
            TokenType::BRACKET_SQUARE_OPEN,
            TokenType::BRACKET_SQUARE_CLOSE,
            TokenType::SYMBOL_OPTCHAIN,
            TokenType::SYMBOL_PERIOD => self::ACCESS,

            TokenType::SYMBOL_EXCLAMATIONMARK => self::UNARY,

            TokenType::SYMBOL_GREATER_THAN,
            TokenType::SYMBOL_GREATER_THAN_OR_EQUAL,
            TokenType::SYMBOL_LESS_THAN,
            TokenType::SYMBOL_LESS_THAN_OR_EQUAL => self::COMPARISON,

            TokenType::SYMBOL_STRICT_EQUALS,
            TokenType::SYMBOL_NOT_EQUALS => self::EQUALITY,

            TokenType::SYMBOL_BOOLEAN_AND => self::LOGICAL_AND,

            TokenType::SYMBOL_NULLISH_COALESCE,
            TokenType::SYMBOL_BOOLEAN_OR => self::LOGICAL_OR,

            TokenType::SYMBOL_QUESTIONMARK,
            TokenType::SYMBOL_COLON => self::TERNARY,

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

    public function mustStopAt(TokenType $tokenType): bool
    {
        return self::forTokenType($tokenType)->value <= $this->value;
    }
}
