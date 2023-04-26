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

namespace PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Narrower;


use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\TypeSystem\Narrower\ExpressionTypeNarrower;
use PackageFactory\ComponentEngine\TypeSystem\Narrower\NarrowedTypes;
use PackageFactory\ComponentEngine\TypeSystem\Narrower\TypeNarrowerContext;
use PackageFactory\ComponentEngine\TypeSystem\Type\NullType\NullType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PackageFactory\ComponentEngine\TypeSystem\Type\UnionType\UnionType;
use PHPUnit\Framework\TestCase;

final class ExpressionTypeNarrowerTest extends TestCase
{
    public function narrowedExpressionsExamples(): mixed
    {
        return [
            'nullableString' => [
                'nullableString',
                $variableIsString = NarrowedTypes::fromEntry('nullableString', StringType::get())
            ],

            'nullableString === null' => [
                'nullableString === null',
                $variableIsNull = NarrowedTypes::fromEntry('nullableString', NullType::get())
            ],
            // Patience you must have my young Padawan.
            'null === nullableString' => [
                'null === nullableString', $variableIsNull
            ],

            'nullableString !== null' => [
                'nullableString !== null', $variableIsString
            ],
            'null !== nullableString' => [
                'null !== nullableString', $variableIsString
            ],

            'true === (nullableString === null)' => [
                'true === (nullableString === null)', $variableIsNull
            ],
            'false !== (nullableString === null)' => [
                'false !== (nullableString === null)', $variableIsNull
            ],

            'false === (nullableString === null)' => [
                'false === (nullableString === null)', $variableIsString
            ],
            'true !== (nullableString === null)' => [
                'true !== (nullableString === null)', $variableIsString
            ],

            'nullableString === variableOfTypeNull' => [
                'nullableString === variableOfTypeNull', $variableIsNull
            ],

            'nullableString === true' => [
                'nullableString === true',
                NarrowedTypes::empty()
            ],
        ];
    }

    /**
     * @dataProvider narrowedExpressionsExamples
     * @test
     */
    public function narrowedExpressions(string $expressionAsString, NarrowedTypes $expectedTypes): void
    {
        $expressionTypeNarrower = new ExpressionTypeNarrower(
            scope: new DummyScope([
                'nullableString' => UnionType::of(StringType::get(), NullType::get()),
                'variableOfTypeNull' => NullType::get()
            ])
        );

        $expressionNode = ExpressionNode::fromString($expressionAsString);

        $actualTypes = $expressionTypeNarrower->narrowTypesOfSymbolsIn($expressionNode, TypeNarrowerContext::TRUTHY);

        $this->assertEqualsCanonicalizing(
            $expectedTypes->toArray(),
            $actualTypes->toArray()
        );
    }
}
