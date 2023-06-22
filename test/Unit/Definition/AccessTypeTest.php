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

namespace PackageFactory\ComponentEngine\Test\Unit\Definition;

use PackageFactory\ComponentEngine\Definition\AccessType;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;
use PHPUnit\Framework\TestCase;

final class AccessTypeTest extends TestCase
{
    /**
     * @return array<string,mixed>
     */
    public static function tokenTypeToAccessTypeExamples(): array
    {
        return [
            TokenType::PERIOD->name => [TokenType::PERIOD, AccessType::MANDATORY],
            TokenType::OPTCHAIN->name => [TokenType::OPTCHAIN, AccessType::OPTIONAL],
        ];
    }

    /**
     * @test
     * @dataProvider tokenTypeToAccessTypeExamples
     * @param TokenType $givenTokenType
     * @param AccessType $expectedAccessType
     * @return void
     */
    public function canBeCreatedFromTokenType(TokenType $givenTokenType, AccessType $expectedAccessType): void
    {
        $this->assertSame($expectedAccessType, AccessType::fromTokenType($givenTokenType));
    }
}
