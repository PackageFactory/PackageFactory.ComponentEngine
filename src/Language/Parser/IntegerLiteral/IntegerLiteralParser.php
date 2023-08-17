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

namespace PackageFactory\ComponentEngine\Language\Parser\IntegerLiteral;

use LogicException;
use PackageFactory\ComponentEngine\Framework\PHP\Singleton\Singleton;
use PackageFactory\ComponentEngine\Language\AST\Node\IntegerLiteral\IntegerFormat;
use PackageFactory\ComponentEngine\Language\AST\Node\IntegerLiteral\IntegerLiteralNode;
use PackageFactory\ComponentEngine\Language\Lexer\Lexer;
use PackageFactory\ComponentEngine\Language\Lexer\LexerException;
use PackageFactory\ComponentEngine\Language\Lexer\Rule\Rule;
use PackageFactory\ComponentEngine\Language\Lexer\Rule\Rules;
use PackageFactory\ComponentEngine\Language\Util\DebugHelper;
use PackageFactory\ComponentEngine\Parser\Source\Range;

final class IntegerLiteralParser
{
    use Singleton;

    private static Rules $INTEGER_TOKEN_TYPES;

    private function __construct()
    {
        self::$INTEGER_TOKEN_TYPES ??= Rules::from(
            Rule::INTEGER_HEXADECIMAL,
            Rule::INTEGER_DECIMAL,
            Rule::INTEGER_OCTAL,
            Rule::INTEGER_BINARY
        );
    }

    public function parse(Lexer $lexer): IntegerLiteralNode
    {
        try {
            $rule = $lexer->readOneOf(self::$INTEGER_TOKEN_TYPES);

            return new IntegerLiteralNode(
                rangeInSource: $lexer->getCursorRange(),
                format: $this->getIntegerFormatFromToken($rule),
                value: $lexer->getBuffer()
            );
        } catch (LexerException $e) {
            throw IntegerLiteralCouldNotBeParsed::becauseOfLexerException($e);
        }
    }

    private function getIntegerFormatFromToken(Rule $rule): IntegerFormat
    {
        return match ($rule) {
            Rule::INTEGER_BINARY => IntegerFormat::BINARY,
            Rule::INTEGER_OCTAL => IntegerFormat::OCTAL,
            Rule::INTEGER_DECIMAL => IntegerFormat::DECIMAL,
            Rule::INTEGER_HEXADECIMAL => IntegerFormat::HEXADECIMAL,
            default => throw new LogicException(
                sprintf(
                    'Expected %s to be one of %s',
                    $rule->value,
                    DebugHelper::describeRules($this->INTEGER_TOKEN_TYPES)
                )
            )
        };
    }
}
