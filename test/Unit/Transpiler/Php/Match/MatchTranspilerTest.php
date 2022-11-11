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

namespace PackageFactory\ComponentEngine\Test\Unit\Transpiler\Php\Match;

use PackageFactory\ComponentEngine\Parser\Ast\EnumDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\MatchNode;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\Transpiler\Php\Match\MatchTranspiler;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumStaticType;
use PHPUnit\Framework\TestCase;

final class MatchTranspilerTest extends TestCase
{
    /**
     * @return array<string,mixed>
     */
    public function matchExamples(): array
    {
        return [
            'match (true) { true -> 42 false -> "foo" }' => [
                'match (true) { true -> 42 false -> "foo" }',
                'match (true) { true => 42, false => \'foo\' }'
            ],
            'match (false) { true -> 42 false -> "foo" }' => [
                'match (false) { true -> 42 false -> "foo" }',
                'match (false) { true => 42, false => \'foo\' }'
            ],
            'match (variableOfTypeBoolean) { true -> 42 false -> "foo" }' => [
                'match (variableOfTypeBoolean) { true -> 42 false -> "foo" }',
                'match ($this->variableOfTypeBoolean) { true => 42, false => \'foo\' }'
            ],
            'match (variableOfTypeBoolean) { true -> variableOfTypeNumber false -> variableOfTypeString }' => [
                'match (variableOfTypeBoolean) { true -> variableOfTypeNumber false -> variableOfTypeString }',
                'match ($this->variableOfTypeBoolean) { true => $this->variableOfTypeNumber, false => $this->variableOfTypeString }'
            ],
            'match (someEnumValue) { SomeEnum.A -> variableOfTypeNumber SomeEnum.B -> variableOfTypeString SomeEnum.C -> variableOfTypeBoolean }' => [
                'match (someEnumValue) { SomeEnum.A -> variableOfTypeNumber SomeEnum.B -> variableOfTypeString SomeEnum.C -> variableOfTypeBoolean }',
                'match ($this->someEnumValue) { SomeEnum::A => $this->variableOfTypeNumber, SomeEnum::B => $this->variableOfTypeString, SomeEnum::C => $this->variableOfTypeBoolean }'
            ],
        ];
    }

    /**
     * @dataProvider matchExamples
     * @test
     * @param string $matchAsString
     * @param string $expectedTranspilationResult
     * @return void
     */
    public function transpilesMatchNodes(string $matchAsString, string $expectedTranspilationResult): void
    {
        $matchTranspiler = new MatchTranspiler(
            scope: new DummyScope([
                'SomeEnum' => EnumStaticType::fromEnumDeclarationNode(
                    EnumDeclarationNode::fromString(
                        'enum SomeEnum { A B C }'
                    )
                )
            ])
        );
        $matchNode = ExpressionNode::fromString($matchAsString)->root;
        assert($matchNode instanceof MatchNode);

        $actualTranspilationResult = $matchTranspiler->transpile(
            $matchNode
        );

        $this->assertEquals(
            $expectedTranspilationResult,
            $actualTranspilationResult
        );
    }
}