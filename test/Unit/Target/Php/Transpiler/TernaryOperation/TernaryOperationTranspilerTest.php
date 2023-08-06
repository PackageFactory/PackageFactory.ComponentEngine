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

namespace PackageFactory\ComponentEngine\Test\Unit\Target\Php\Transpiler\TernaryOperation;

use PackageFactory\ComponentEngine\Domain\PropertyName\PropertyName;
use PackageFactory\ComponentEngine\Domain\StructName\StructName;
use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\Domain\TypeName\TypeNames;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\TernaryOperation\TernaryOperationTranspiler;
use PackageFactory\ComponentEngine\Test\Unit\Language\ASTNodeFixtures;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StructType\Properties;
use PackageFactory\ComponentEngine\TypeSystem\Type\StructType\Property;
use PackageFactory\ComponentEngine\TypeSystem\Type\StructType\StructType;
use PackageFactory\ComponentEngine\TypeSystem\TypeReference;
use PHPUnit\Framework\TestCase;

final class TernaryOperationTranspilerTest extends TestCase
{
    /**
     * @return array<string,mixed>
     */
    public static function ternaryOperationExamples(): array
    {
        return [
            'true ? 42 : "foo"' => ['true ? 42 : "foo"', '(true ? 42 : \'foo\')'],
            '(true) ? 42 : "foo"' => ['(true) ? 42 : "foo"', '(true ? 42 : \'foo\')'],
            'a ? 42 : "foo"' => ['a ? 42 : "foo"', '($this->a ? 42 : \'foo\')'],
            'true ? b : "foo"' => ['true ? b : "foo"', '(true ? $this->b : \'foo\')'],
            'true ? 42 : c' => ['true ? 42 : c', '(true ? 42 : $this->c)'],
            'a ? b : c' => ['a ? b : c', '($this->a ? $this->b : $this->c)'],
            'false ? 42 : "foo"' => ['false ? 42 : "foo"', '(false ? 42 : \'foo\')'],
            '1 < 2 ? 42 : "foo"' => ['1 < 2 ? 42 : "foo"', '((1 < 2) ? 42 : \'foo\')']
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public static function ternaryOperationWithVariablesInConditionExamples(): array
    {
        return [
            'true === someString ? "a" : "foo"' => ['true === someString ? "a" : "foo"', '((true === $this->someString) ? \'a\' : \'foo\')'],
            'true === someStruct.foo ? "a" : "foo"' => ['true === someStruct.foo ? "a" : "foo"', '((true === $this->someStruct->foo) ? \'a\' : \'foo\')'],
            'true === someStruct.deep.foo ? "a" : "foo"' => ['true === someStruct.deep.foo ? "a" : "foo"', '((true === $this->someStruct->deep->foo) ? \'a\' : \'foo\')'],
            'someStruct.foo === true ? "a" : "foo"' => ['someStruct.foo === true ? "a" : "foo"', '(($this->someStruct->foo === true) ? \'a\' : \'foo\')'],
            'someStruct.foo === true || false ? "a" : "foo"' => ['someStruct.foo === true || false ? "a" : "foo"', '((($this->someStruct->foo === true) || false) ? \'a\' : \'foo\')'],
            '1 < 2 === a || 5 > b || c === true && false ? "a" : "foo"' => ['1 < 2 === a || 5 > b || c === true && false ? "a" : "foo"', '(((((1 < 2) === $this->a) || (5 > $this->b)) || (($this->c === true) && false)) ? \'a\' : \'foo\')'],
        ];
    }

    /**
     * @dataProvider ternaryOperationExamples
     * @dataProvider ternaryOperationWithVariablesInConditionExamples
     * @test
     * @param string $ternaryOperationAsString
     * @param string $expectedTranspilationResult
     * @return void
     */
    public function transpilesTernaryOperationNodes(string $ternaryOperationAsString, string $expectedTranspilationResult): void
    {
        $ternaryOperationTranspiler = new TernaryOperationTranspiler(
            scope: new DummyScope(
                [
                    StringType::singleton(),
                    $someStructType = new StructType(
                        name: StructName::from('SomeStruct'),
                        properties: new Properties(
                            new Property(
                                name: PropertyName::from('foo'),
                                type: new TypeReference(
                                    names: new TypeNames(TypeName::from('string')),
                                    isOptional: false,
                                    isArray: false
                                )
                            ),
                            new Property(
                                name: PropertyName::from('deep'),
                                type: new TypeReference(
                                    names: new TypeNames(TypeName::from('SomeStruct')),
                                    isOptional: true,
                                    isArray: false
                                )
                            )
                        )
                    )
                ],
                [
                    'someString' => StringType::singleton(),
                    'someStruct' => $someStructType
                ]
            )
        );
        $ternaryOperationNode = ASTNodeFixtures::TernaryOperation($ternaryOperationAsString);

        $actualTranspilationResult = $ternaryOperationTranspiler->transpile(
            $ternaryOperationNode
        );

        $this->assertEquals(
            $expectedTranspilationResult,
            $actualTranspilationResult
        );
    }
}
