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

namespace PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Resolver\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\Expression\ExpressionTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Type\BooleanType\BooleanType;
use PackageFactory\ComponentEngine\TypeSystem\Type\NullType\NullType;
use PackageFactory\ComponentEngine\TypeSystem\Type\NumberType\NumberType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PackageFactory\ComponentEngine\TypeSystem\Type\UnionType\UnionType;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;
use PHPUnit\Framework\TestCase;

final class ExpressionTypeResolverTest extends TestCase
{
    /**
     * @return array<string,mixed>
     */
    public function binaryOperationExamples(): array
    {
        return [
            'true && false' => ['true && false', BooleanType::get()],
            'true || false' => ['true || false', BooleanType::get()],
            'true && "foo"' => ['true && "foo"', UnionType::of(BooleanType::get(), StringType::get())],
            'true || "foo"' => ['true || "foo"', UnionType::of(BooleanType::get(), StringType::get())],
            'true && 42' => ['true && 42', UnionType::of(BooleanType::get(), NumberType::get())],
            'true || 42' => ['true || 42', UnionType::of(BooleanType::get(), NumberType::get())],

            '1 + 2' => ['1 + 2', NumberType::get()],
            '2 - 1' => ['2 - 1', NumberType::get()],
            '2 * 4' => ['2 * 4', NumberType::get()],
            '2 / 4' => ['2 / 4', NumberType::get()],
            '2 % 4' => ['2 % 4', NumberType::get()],

            '4 === 2' => ['4 === 2', BooleanType::get()],
            '4 !== 2' => ['4 !== 2', BooleanType::get()],
            '4 > 2' => ['4 > 2', BooleanType::get()],
            '4 >= 2' => ['4 >= 2', BooleanType::get()],
            '4 < 2' => ['4 < 2', BooleanType::get()],
            '4 <= 2' => ['4 <= 2', BooleanType::get()],
        ];
    }

    /**
     * @dataProvider binaryOperationExamples
     * @test
     * @param string $binaryExpressionAsString
     * @param TypeInterface $expectedType
     * @return void
     */
    public function resolvesBinaryOperationToResultingType(string $binaryExpressionAsString, TypeInterface $expectedType): void
    {
        $scope = new DummyScope();
        $expressionTypeResolver = new ExpressionTypeResolver(scope: $scope);
        $expressionNode = ExpressionNode::fromString($binaryExpressionAsString);

        $actualType = $expressionTypeResolver->resolveTypeOf($expressionNode);

        $this->assertTrue(
            $expectedType->is($actualType),
            sprintf('Expected %s, got %s', $expectedType::class, $actualType::class)
        );
    }

    /**
     * @test
     * @return void
     */
    public function resolvesBooleanLiteralToBooleanType(): void
    {
        $scope = new DummyScope();
        $expressionTypeResolver = new ExpressionTypeResolver(scope: $scope);
        $expressionNode = ExpressionNode::fromString('true');

        $expectedType = BooleanType::get();
        $actualType = $expressionTypeResolver->resolveTypeOf($expressionNode);

        $this->assertTrue(
            $expectedType->is($actualType),
            sprintf('Expected %s, got %s', $expectedType::class, $actualType::class)
        );
    }

    /**
     * @test
     * @return void
     */
    public function resolvesKnownIdentifierToItsType(): void
    {
        $scope = new DummyScope(['foo' => StringType::get()]);
        $expressionTypeResolver = new ExpressionTypeResolver(scope: $scope);
        $expressionNode = ExpressionNode::fromString('foo');

        $expectedType = StringType::get();
        $actualType = $expressionTypeResolver->resolveTypeOf($expressionNode);

        $this->assertTrue(
            $expectedType->is($actualType),
            sprintf('Expected %s, got %s', $expectedType::class, $actualType::class)
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function matchExamples(): array
    {
        return [
            'match (true) { true -> 42 false -> "foo" }' => [
                'match (true) { true -> 42 false -> "foo" }',
                NumberType::get()
            ],
            'match (false) { true -> 42 false -> "foo" }' => [
                'match (false) { true -> 42 false -> "foo" }',
                StringType::get()
            ],
            'match (variableOfTypeBoolean) { true -> 42 false -> "foo" }' => [
                'match (variableOfTypeBoolean) { true -> 42 false -> "foo" }',
                UnionType::of(NumberType::get(), StringType::get())
            ],
            'match (variableOfTypeBoolean) { true -> variableOfTypeNumber false -> variableOfTypeString }' => [
                'match (variableOfTypeBoolean) { true -> variableOfTypeNumber false -> variableOfTypeString }',
                UnionType::of(NumberType::get(), StringType::get())
            ],
        ];
    }

    /**
     * @dataProvider matchExamples
     * @test
     * @param string $matchAsString
     * @param TypeInterface $expectedType
     * @return void
     */
    public function resolvesMatchToResultingType(string $matchAsString, TypeInterface $expectedType): void
    {
        $scope = new DummyScope([
            'variableOfTypeBoolean' => BooleanType::get(),
            'variableOfTypeString' => StringType::get(),
            'variableOfTypeNumber' => NumberType::get(),
        ]);
        $expressionTypeResolver = new ExpressionTypeResolver(scope: $scope);
        $expressionNode = ExpressionNode::fromString($matchAsString);

        $actualType = $expressionTypeResolver->resolveTypeOf($expressionNode);

        $this->assertTrue(
            $expectedType->is($actualType),
            sprintf('Expected %s, got %s', $expectedType::class, $actualType::class)
        );
    }

    /**
     * @test
     * @return void
     */
    public function resolvesNullLiteralToNullType(): void
    {
        $scope = new DummyScope();
        $expressionTypeResolver = new ExpressionTypeResolver(scope: $scope);
        $expressionNode = ExpressionNode::fromString('null');

        $expectedType = NullType::get();
        $actualType = $expressionTypeResolver->resolveTypeOf($expressionNode);

        $this->assertTrue(
            $expectedType->is($actualType),
            sprintf('Expected %s, got %s', $expectedType::class, $actualType::class)
        );
    }

    /**
     * @test
     * @return void
     */
    public function resolvesNumberLiteralToNumberType(): void
    {
        $scope = new DummyScope();
        $expressionTypeResolver = new ExpressionTypeResolver(scope: $scope);
        $expressionNode = ExpressionNode::fromString('42');

        $expectedType = NumberType::get();
        $actualType = $expressionTypeResolver->resolveTypeOf($expressionNode);

        $this->assertTrue(
            $expectedType->is($actualType),
            sprintf('Expected %s, got %s', $expectedType::class, $actualType::class)
        );
    }

    /**
     * @test
     * @return void
     */
    public function resolvesStringLiteralToStringType(): void
    {
        $scope = new DummyScope();
        $expressionTypeResolver = new ExpressionTypeResolver(scope: $scope);
        $expressionNode = ExpressionNode::fromString('"foo"');

        $expectedType = StringType::get();
        $actualType = $expressionTypeResolver->resolveTypeOf($expressionNode);

        $this->assertTrue(
            $expectedType->is($actualType),
            sprintf('Expected %s, got %s', $expectedType::class, $actualType::class)
        );
    }

    /**
     * @test
     * @return void
     */
    public function resolvesTagToStringType(): void
    {
        $scope = new DummyScope();
        $expressionTypeResolver = new ExpressionTypeResolver(scope: $scope);
        $expressionNode = ExpressionNode::fromString('<div></div>');

        $expectedType = StringType::get();
        $actualType = $expressionTypeResolver->resolveTypeOf($expressionNode);

        $this->assertTrue(
            $expectedType->is($actualType),
            sprintf('Expected %s, got %s', $expectedType::class, $actualType::class)
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function templateLiteralExamples(): array
    {
        return [
            '`Hello World`' => ['`Hello World`'],
            '`Hello ${name}`' => ['`Hello ${name}`'],
            '`${greeting} World`' => ['`${greeting} World`'],
            '`Hello ${name}! How are you?`' => ['`Hello ${name}! How are you?`'],
        ];
    }

    /**
     * @dataProvider templateLiteralExamples
     * @test
     * @param string $templateLiteralAsString
     * @return void
     */
    public function resolvesTemplateLiteralToStringType(string $templateLiteralAsString): void
    {
        $scope = new DummyScope();
        $expressionTypeResolver = new ExpressionTypeResolver(scope: $scope);
        $expressionNode = ExpressionNode::fromString($templateLiteralAsString);

        $expectedType = StringType::get();
        $actualType = $expressionTypeResolver->resolveTypeOf($expressionNode);

        $this->assertTrue(
            $expectedType->is($actualType),
            sprintf('Expected %s, got %s', $expectedType::class, $actualType::class)
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function ternaryOperationExamples(): array
    {
        return [
            'true ? 42 : "foo"' => ['true ? 42 : "foo"', NumberType::get()],
            'false ? 42 : "foo"' => ['false ? 42 : "foo"', StringType::get()],
            '1 < 2 ? 42 : "foo"' => ['1 < 2 ? 42 : "foo"', UnionType::of(NumberType::get(), StringType::get())]
        ];
    }

    /**
     * @dataProvider ternaryOperationExamples
     * @test
     * @param string $ternaryOperationAsString
     * @param TypeInterface $expectedType
     * @return void
     */
    public function resolvesTernaryOperationToResultingType(string $ternaryOperationAsString, TypeInterface $expectedType): void
    {
        $scope = new DummyScope();
        $expressionTypeResolver = new ExpressionTypeResolver(scope: $scope);
        $expressionNode = ExpressionNode::fromString($ternaryOperationAsString);

        $actualType = $expressionTypeResolver->resolveTypeOf($expressionNode);

        $this->assertTrue(
            $expectedType->is($actualType),
            sprintf('Expected %s, got %s', $expectedType::class, $actualType::class)
        );
    }
}
