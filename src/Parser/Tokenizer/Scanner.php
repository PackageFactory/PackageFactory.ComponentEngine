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

use PackageFactory\ComponentEngine\Parser\Source\Source;

final class Scanner
{
    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return void
     */
    public static function assertValid(\Iterator $tokens): void
    {
        if (!$tokens->valid()) {
            throw new \Exception("@TODO: Unexpected end of file.");
        }
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @param TokenType ...$types
     * @return void
     */
    public static function assertType(\Iterator $tokens, TokenType ...$types): void
    {
        self::assertValid($tokens);

        $actualType = $tokens->current()->type;
        foreach ($types as $expectedType) {
            if ($actualType === $expectedType) {
                return;
            }
        }

        throw new \Exception(
            "@TODO: Unexpected token: "
                . $actualType->value
                . " at "
                . ($tokens->current()->boundaries->start->rowIndex + 1)
                . ":"
                . ($tokens->current()->boundaries->start->columnIndex + 1)
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @param string ...$values
     * @return void
     */
    public static function assertValue(\Iterator $tokens, string ...$values): void
    {
        self::assertValid($tokens);

        $actualValue = $tokens->current()->value;
        foreach ($values as $expectedValue) {
            if ($actualValue === $expectedValue) {
                return;
            }
        }

        throw new \Exception("@TODO: Unexpected value: " . $actualValue);
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return \Iterator<mixed,Token>
     */
    public static function skipOne(\Iterator $tokens): \Iterator
    {
        $tokens->next();
        return $tokens;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return void
     */
    public static function skipSpace(\Iterator $tokens): void
    {
        while (
            $tokens->valid() && match ($tokens->current()->type) {
                TokenType::SPACE,
                TokenType::END_OF_LINE => true,
                default => false
            }
        ) {
            $tokens->next();
        }
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return void
     */
    public static function skipSpaceAndComments(\Iterator $tokens): void
    {
        while (
            $tokens->valid() && match ($tokens->current()->type) {
                TokenType::SPACE,
                TokenType::END_OF_LINE,
                TokenType::COMMENT => true,
                default => false
            }
        ) {
            $tokens->next();
        }
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return string
     */
    public static function value(\Iterator $tokens): string
    {
        self::assertValid($tokens);
        return $tokens->current()->value;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return TokenType
     */
    public static function type(\Iterator $tokens): TokenType
    {
        self::assertValid($tokens);
        return $tokens->current()->type;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return Source
     */
    public static function source(\Iterator $tokens): Source
    {
        self::assertValid($tokens);
        return $tokens->current()->source;
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return bool
     */
    public static function isEnd(\Iterator $tokens): bool
    {
        return !$tokens->valid();
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     */
    public static function debugPrint(\Iterator $tokens): string
    {
        $tokensAsArray = [];
        while ($tokens->valid()) {
            $tokensAsArray[] = [
                "type" => $tokens->current()->type,
                "value" => $tokens->current()->value
            ];
            $tokens->next();
        }
        return json_encode($tokensAsArray, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    }
}
