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

use PackageFactory\ComponentEngine\Parser\Lexer\Scope\TemplateLiteral;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;
use PackageFactory\ComponentEngine\Test\Util\TokenizerTestTrait;
use PHPUnit\Framework\TestCase;

final class TemplateLiteralTest extends TestCase
{
    use TokenizerTestTrait;

    /**
     * @return array<string, array{string, array<int, array{TokenType, string}>}>
     */
    public function provider(): array
    {
        return [
            'simple' => [
                '`Hello World`',
                [
                    [TokenType::TEMPLATE_LITERAL_START, '`'],
                    [TokenType::TEMPLATE_LITERAL_CONTENT, 'Hello World'],
                    [TokenType::TEMPLATE_LITERAL_END, '`'],
                ]
            ],
            'simple with escapes' => [
                '`Hello \`World\``',
                [
                    [TokenType::TEMPLATE_LITERAL_START, '`'],
                    [TokenType::TEMPLATE_LITERAL_CONTENT, 'Hello '],
                    [TokenType::TEMPLATE_LITERAL_ESCAPE, '\\'],
                    [TokenType::TEMPLATE_LITERAL_ESCAPED_CHARACTER, '`'],
                    [TokenType::TEMPLATE_LITERAL_CONTENT, 'World'],
                    [TokenType::TEMPLATE_LITERAL_ESCAPE, '\\'],
                    [TokenType::TEMPLATE_LITERAL_ESCAPED_CHARACTER, '`'],
                    [TokenType::TEMPLATE_LITERAL_END, '`'],
                ]
            ],
            'simple exit after delimiter' => [
                '`Hello World` ',
                [
                    [TokenType::TEMPLATE_LITERAL_START, '`'],
                    [TokenType::TEMPLATE_LITERAL_CONTENT, 'Hello World'],
                    [TokenType::TEMPLATE_LITERAL_END, '`'],
                ]
            ],
            'interpolation' => [
                '`Hello ${}`',
                [
                    [TokenType::TEMPLATE_LITERAL_START, '`'],
                    [TokenType::TEMPLATE_LITERAL_CONTENT, 'Hello '],
                    [TokenType::TEMPLATE_LITERAL_INTERPOLATION_START, '${'],
                    [TokenType::TEMPLATE_LITERAL_INTERPOLATION_END, '}'],
                    [TokenType::TEMPLATE_LITERAL_END, '`'],
                ]
            ],
            'interpolation with escapes' => [
                '`Hello \`${}\``',
                [
                    [TokenType::TEMPLATE_LITERAL_START, '`'],
                    [TokenType::TEMPLATE_LITERAL_CONTENT, 'Hello '],
                    [TokenType::TEMPLATE_LITERAL_ESCAPE, '\\'],
                    [TokenType::TEMPLATE_LITERAL_ESCAPED_CHARACTER, '`'],
                    [TokenType::TEMPLATE_LITERAL_INTERPOLATION_START, '${'],
                    [TokenType::TEMPLATE_LITERAL_INTERPOLATION_END, '}'],
                    [TokenType::TEMPLATE_LITERAL_ESCAPE, '\\'],
                    [TokenType::TEMPLATE_LITERAL_ESCAPED_CHARACTER, '`'],
                    [TokenType::TEMPLATE_LITERAL_END, '`'],
                ]
            ],
            'interpolation exit after delimiter' => [
                '`Hello ${}` ',
                [
                    [TokenType::TEMPLATE_LITERAL_START, '`'],
                    [TokenType::TEMPLATE_LITERAL_CONTENT, 'Hello '],
                    [TokenType::TEMPLATE_LITERAL_INTERPOLATION_START, '${'],
                    [TokenType::TEMPLATE_LITERAL_INTERPOLATION_END, '}'],
                    [TokenType::TEMPLATE_LITERAL_END, '`'],
                ]
            ],
            'multiple interpolations' => [
                '`Hello ${}! ${}. Goodbye!`',
                [
                    [TokenType::TEMPLATE_LITERAL_START, '`'],
                    [TokenType::TEMPLATE_LITERAL_CONTENT, 'Hello '],
                    [TokenType::TEMPLATE_LITERAL_INTERPOLATION_START, '${'],
                    [TokenType::TEMPLATE_LITERAL_INTERPOLATION_END, '}'],
                    [TokenType::TEMPLATE_LITERAL_CONTENT, '! '],
                    [TokenType::TEMPLATE_LITERAL_INTERPOLATION_START, '${'],
                    [TokenType::TEMPLATE_LITERAL_INTERPOLATION_END, '}'],
                    [TokenType::TEMPLATE_LITERAL_CONTENT, '. Goodbye!'],
                    [TokenType::TEMPLATE_LITERAL_END, '`'],
                ]
            ],
            'characters "{" and "}"' => [
                '`Hello }! This should just {work}.`',
                [
                    [TokenType::TEMPLATE_LITERAL_START, '`'],
                    [TokenType::TEMPLATE_LITERAL_CONTENT, 'Hello }! This should just {work}.'],
                    [TokenType::TEMPLATE_LITERAL_END, '`'],
                ]
            ],
            'character "$"' => [
                '`Price: $99`',
                [
                    [TokenType::TEMPLATE_LITERAL_START, '`'],
                    [TokenType::TEMPLATE_LITERAL_CONTENT, 'Price: $99'],
                    [TokenType::TEMPLATE_LITERAL_END, '`'],
                ]
            ],
            'escaped interpolation' => [
                '`Hello \${test}!`',
                [
                    [TokenType::TEMPLATE_LITERAL_START, '`'],
                    [TokenType::TEMPLATE_LITERAL_CONTENT, 'Hello '],
                    [TokenType::TEMPLATE_LITERAL_ESCAPE, '\\'],
                    [TokenType::TEMPLATE_LITERAL_ESCAPED_CHARACTER, '$'],
                    [TokenType::TEMPLATE_LITERAL_CONTENT, '{test}!'],
                    [TokenType::TEMPLATE_LITERAL_END, '`'],
                ]
            ],
            'dollar signs only' => [
                '`$$$`',
                [
                    [TokenType::TEMPLATE_LITERAL_START, '`'],
                    [TokenType::TEMPLATE_LITERAL_CONTENT, '$$$'],
                    [TokenType::TEMPLATE_LITERAL_END, '`'],
                ]
            ],
            'without delimiter' => [
                'Hello \${test}!',
                [
                    [TokenType::TEMPLATE_LITERAL_CONTENT, 'Hello '],
                    [TokenType::TEMPLATE_LITERAL_ESCAPE, '\\'],
                    [TokenType::TEMPLATE_LITERAL_ESCAPED_CHARACTER, '$'],
                    [TokenType::TEMPLATE_LITERAL_CONTENT, '{test}!'],
                ]
            ],
            'with line break' => [
                '`Hello ' . PHP_EOL . ' World`',
                [
                    [TokenType::TEMPLATE_LITERAL_START, '`'],
                    [TokenType::TEMPLATE_LITERAL_CONTENT, 'Hello '],
                    [TokenType::END_OF_LINE, PHP_EOL],
                    [TokenType::TEMPLATE_LITERAL_CONTENT, ' World'],
                    [TokenType::TEMPLATE_LITERAL_END, '`'],
                ]
            ],
            'with interpolation and line break' => [
                '`Hello ' . PHP_EOL . ' ${name}`',
                [
                    [TokenType::TEMPLATE_LITERAL_START, '`'],
                    [TokenType::TEMPLATE_LITERAL_CONTENT, 'Hello '],
                    [TokenType::END_OF_LINE, PHP_EOL],
                    [TokenType::TEMPLATE_LITERAL_CONTENT, ' '],
                    [TokenType::TEMPLATE_LITERAL_INTERPOLATION_START, '${'],
                    [TokenType::IDENTIFIER, 'name'],
                    [TokenType::TEMPLATE_LITERAL_INTERPOLATION_END, '}'],
                    [TokenType::TEMPLATE_LITERAL_END, '`'],
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
        $this->assertTokenStream($tokens, TemplateLiteral::tokenize($iterator));
    }
}
