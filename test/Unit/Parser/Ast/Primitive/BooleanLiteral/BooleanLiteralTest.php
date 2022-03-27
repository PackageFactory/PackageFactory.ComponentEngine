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

namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Ast\Primitive\BooleanLiteral;

use PackageFactory\ComponentEngine\Parser\Ast\Primitive\BooleanLiteral\BooleanLiteral;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenStream\TokenStream;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PHPUnit\Framework\TestCase;

final class BooleanLiteralTest extends TestCase
{
    /**
     * @test
     * @small
     */
    public function canPeekIntoTokenStream(): void
    {
        $source = Source::fromString('true');
        $tokenizer = Tokenizer::fromSource($source);
        $stream = TokenStream::fromTokenizer($tokenizer);

        $this->assertTrue(BooleanLiteral::peekInto($stream));

        $source = Source::fromString('false');
        $tokenizer = Tokenizer::fromSource($source);
        $stream = TokenStream::fromTokenizer($tokenizer);

        $this->assertTrue(BooleanLiteral::peekInto($stream));
    }

    /**
     * @test
     * @small
     */
    public function canBeCreatedFromTrueKeywordToken(): void
    {
        $source = Source::fromString('true');
        $tokenizer = Tokenizer::fromSource($source);

        $result = BooleanLiteral::fromToken($tokenizer->getIterator()->current());

        $this->assertEquals('true', (string) $result);
        $this->assertJsonStringEqualsJsonString(
            (string) json_encode([
                'type' => 'BooleanLiteral',
                'boundaries' => [
                    'start' => 0,
                    'end' => 3
                ],
                'value' => 'true'
            ]),
            (string) json_encode($result)
        );
    }

    /**
     * @test
     * @small
     */
    public function canBeCreatedFromFalseKeywordToken(): void
    {
        $source = Source::fromString('false');
        $tokenizer = Tokenizer::fromSource($source);

        $result = BooleanLiteral::fromToken($tokenizer->getIterator()->current());

        $this->assertEquals('false', (string) $result);
        $this->assertJsonStringEqualsJsonString(
            (string) json_encode([
                'type' => 'BooleanLiteral',
                'boundaries' => [
                    'start' => 0,
                    'end' => 4
                ],
                'value' => 'false'
            ]),
            (string) json_encode($result)
        );
    }
}
