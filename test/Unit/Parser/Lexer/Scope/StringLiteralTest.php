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

namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Lexer\Scope;

use PackageFactory\ComponentEngine\Parser\Lexer\Scope\StringLiteral;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;
use PackageFactory\ComponentEngine\Test\Util\TokenizerTestTrait;
use PHPUnit\Framework\TestCase;

final class StringLiteralTest extends TestCase
{
    use TokenizerTestTrait;

    /**
     * @return array<string, array{string, array<int, array{TokenType, string}>}>
     */
    public function provider(): array
    {
        return [
            'double-quote simple' => [
                '"Hello World"',
                [
                    [TokenType::STRING_LITERAL_START, '"'],
                    [TokenType::STRING_LITERAL_CONTENT, 'Hello World'],
                    [TokenType::STRING_LITERAL_END, '"'],
                ]
            ],
            'double-quote with escapes' => [
                '"Hello \"World\""',
                [
                    [TokenType::STRING_LITERAL_START, '"'],
                    [TokenType::STRING_LITERAL_CONTENT, 'Hello '],
                    [TokenType::STRING_LITERAL_ESCAPE, '\\'],
                    [TokenType::STRING_LITERAL_ESCAPED_CHARACTER, '"'],
                    [TokenType::STRING_LITERAL_CONTENT, 'World'],
                    [TokenType::STRING_LITERAL_ESCAPE, '\\'],
                    [TokenType::STRING_LITERAL_ESCAPED_CHARACTER, '"'],
                    [TokenType::STRING_LITERAL_END, '"'],
                ]
            ],
            'single-quote simple' => [
                '\'Hello World\'',
                [
                    [TokenType::STRING_LITERAL_START, '\''],
                    [TokenType::STRING_LITERAL_CONTENT, 'Hello World'],
                    [TokenType::STRING_LITERAL_END, '\''],
                ]
            ],
            'single-quote with escapes' => [
                '\'Hello \\\'World\\\'\'',
                [
                    [TokenType::STRING_LITERAL_START, '\''],
                    [TokenType::STRING_LITERAL_CONTENT, 'Hello '],
                    [TokenType::STRING_LITERAL_ESCAPE, '\\'],
                    [TokenType::STRING_LITERAL_ESCAPED_CHARACTER, '\''],
                    [TokenType::STRING_LITERAL_CONTENT, 'World'],
                    [TokenType::STRING_LITERAL_ESCAPE, '\\'],
                    [TokenType::STRING_LITERAL_ESCAPED_CHARACTER, '\''],
                    [TokenType::STRING_LITERAL_END, '\''],
                ]
            ],
            'double-quote exit after delimiter' => [
                '"Hello World" ',
                [
                    [TokenType::STRING_LITERAL_START, '"'],
                    [TokenType::STRING_LITERAL_CONTENT, 'Hello World'],
                    [TokenType::STRING_LITERAL_END, '"'],
                ]
            ],
            'single-quote exit after delimiter' => [
                '\'Hello World\' ',
                [
                    [TokenType::STRING_LITERAL_START, '\''],
                    [TokenType::STRING_LITERAL_CONTENT, 'Hello World'],
                    [TokenType::STRING_LITERAL_END, '\''],
                ]
            ],
            'double-quote exit after newline' => [
                '"Hello ' . PHP_EOL . ' World"',
                [
                    [TokenType::STRING_LITERAL_START, '"'],
                    [TokenType::STRING_LITERAL_CONTENT, 'Hello '],
                ]
            ],
            'single-quote exit after newline' => [
                '\'Hello ' . PHP_EOL . ' World\'',
                [
                    [TokenType::STRING_LITERAL_START, '\''],
                    [TokenType::STRING_LITERAL_CONTENT, 'Hello '],
                ]
            ],
            'without delimiter' => [
                'Hello World',
                [
                    [TokenType::STRING_LITERAL_CONTENT, 'Hello World'],
                ]
            ],
        ];
    }

    /**
     * @dataProvider provider
     * @test
     * @small
     * @param string $input
     * @param array<int, array{TokenType, string}> $tokens
     * @return void
     */
    public function test(string $input, array $tokens): void
    {
        $iterator = SourceIterator::fromSource(Source::fromString($input));
        $this->assertTokenStream($tokens, StringLiteral::tokenize($iterator));
    }
}
