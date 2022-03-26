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

namespace PackageFactory\ComponentEngine\Test\Util;

use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Count;
use PHPUnit\Framework\Constraint\IsIdentical;

trait TokenizerTestTrait
{
    /**
     * @param array<int, array{TokenType, string}> $expected
     * @param \Iterator<Token> $actual
     * @return void
     */
    public function assertTokenStream(array $expected, \Iterator $actual): void
    {
        $actual = iterator_to_array($actual, false);

        $index = 0;
        foreach ($actual as $token) {
            if (isset($expected[$index])) {
                Assert::assertThat($token->value, new IsIdentical($expected[$index][1]), 'At index ' . $index);
                Assert::assertThat($token->type, new IsIdentical($expected[$index][0]), 'At index ' . $index);
            }
            $index++;
        }

        Assert::assertThat($actual, new Count(count($expected)));
    }
}
