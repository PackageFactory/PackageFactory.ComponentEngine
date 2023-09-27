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

namespace PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Resolver\TemplateLiteral;

use PackageFactory\ComponentEngine\Test\Unit\Language\ASTNodeFixtures;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\TemplateLiteral\TemplateLiteralTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PHPUnit\Framework\TestCase;

final class TemplateLiteralTypeResolverTest extends TestCase
{
    /**
     * @return iterable<mixed>
     */
    public static function templateLiteralExamples(): iterable
    {
        $source = <<<EOF
        """
        Hello world
        """
        EOF;
        yield $source => [$source];

        $source = <<<EOF
        """
        Hello {name}
        """
        EOF;
        yield $source => [$source];

        $source = <<<EOF
        """
        {greeting} World
        """
        EOF;
        yield $source => [$source];

        $source = <<<EOF
        """
        Hello {name}! How are you?
        """
        EOF;
        yield $source => [$source];
    }

    /**
     * @dataProvider templateLiteralExamples
     * @test
     * @param string $templateLiteralAsString
     * @return void
     */
    public function resolvesTemplateLiteralToStringType(string $templateLiteralAsString): void
    {
        $templateLiteralTypeResolver = new TemplateLiteralTypeResolver();
        $templateLiteralNode = ASTNodeFixtures::TemplateLiteral($templateLiteralAsString);

        $expectedType = StringType::singleton();
        $actualType = $templateLiteralTypeResolver->resolveTypeOf($templateLiteralNode);

        $this->assertTrue(
            $expectedType->is($actualType),
            sprintf('Expected %s, got %s', $expectedType::class, $actualType::class)
        );
    }
}
