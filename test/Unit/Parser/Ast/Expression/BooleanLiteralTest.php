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

namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\BooleanLiteral;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PHPUnit\Framework\TestCase;

final class BooleanLiteralTest extends TestCase
{
    /**
     * @return array<string, array{string, string, boolean, array<mixed>}>
     */
    public function provider(): array
    {
        return [
            'true' => [
                'true',
                'true',
                true,
                [
                    'type' => 'BooleanLiteral',
                    'offset' => [0, 3],
                    'value' => 'true'
                ],
            ],
            'false' => [
                'false',
                'false',
                false,
                [
                    'type' => 'BooleanLiteral',
                    'offset' => [0, 4],
                    'value' => 'false'
                ],
            ],
        ];
    }

    /**
     * @dataProvider provider
     * @test
     * @small
     * @param string $input
     * @param string $asString
     * @param boolean $asBoolean
     * @param array<mixed> $asJson
     * @return void
     */
    public function test(string $input, string $asString, bool $asBoolean, array $asJson): void
    {
        $source = Source::fromString($input);
        $tokenizer = Tokenizer::fromSource($source, Scope\Expression::class);
        $stream = TokenStream::fromTokenizer($tokenizer);

        $result = BooleanLiteral::fromTokenStream($stream);

        $this->assertEquals($asString, (string) $result);
        $this->assertEquals($asBoolean, $result->boolean);
        $this->assertJsonStringEqualsJsonString(
            (string) json_encode($asJson),
            (string) json_encode($result)
        );
    }
}
