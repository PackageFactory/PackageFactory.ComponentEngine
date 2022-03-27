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

namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Ast\Primitive\NullLiteral;

use PackageFactory\ComponentEngine\Parser\Ast\Primitive\NullLiteral\NullLiteral;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenStream\TokenStream;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PHPUnit\Framework\TestCase;

final class NullLiteralTest extends TestCase
{
    /**
     * @test
     * @small
     */
    public function canPeekIntoTokenStream(): void
    {
        $source = Source::fromString('null');
        $tokenizer = Tokenizer::fromSource($source);
        $stream = TokenStream::fromTokenizer($tokenizer);

        $this->assertTrue(NullLiteral::peekInto($stream));
    }

    /**
     * @test
     * @small
     */
    public function canBeCreatedFromNullKeywordToken(): void
    {
        $source = Source::fromString('null');
        $tokenizer = Tokenizer::fromSource($source);

        $result = NullLiteral::fromToken($tokenizer->getIterator()->current());

        $this->assertEquals('null', (string) $result);
        $this->assertJsonStringEqualsJsonString(
            (string) json_encode([
                'type' => 'NullLiteral',
                'boundaries' => [
                    'start' => 0,
                    'end' => 3
                ]
            ]),
            (string) json_encode($result)
        );
    }
}
