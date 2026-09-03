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

use PackageFactory\ComponentEngine\Test\Unit\Language\ASTNodeFixtures;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\Expression\ExpressionTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Type\BooleanType\BooleanType;
use PackageFactory\ComponentEngine\TypeSystem\Type\NullType\NullType;
use PackageFactory\ComponentEngine\TypeSystem\Type\IntegerType\IntegerType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PackageFactory\ComponentEngine\TypeSystem\Type\UnionType\UnionType;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;
use PHPUnit\Framework\TestCase;

final class ExpressionTypeResolverTest extends TestCase
{
    /**
     * @return array<string,mixed>
     */
    public static function binaryOperationExamples(): array
    {
        return [
            'true && false' => ['true && false', BooleanType::singleton()],
            'true || false' => ['true || false', BooleanType::singleton()],
            'true && "foo"' => ['true && "foo"', UnionType::of(BooleanType::singleton(), StringType::singleton())],
            'true || "foo"' => ['true || "foo"', UnionType::of(BooleanType::singleton(), StringType::singleton())],
            'true && 42' => ['true && 42', UnionType::of(BooleanType::singleton(), IntegerType::singleton())],
            'true || 42' => ['true || 42', UnionType::of(BooleanType::singleton(), IntegerType::singleton())],

            '4 === 2' => ['4 === 2', BooleanType::singleton()],
            '4 !== 2' => ['4 !== 2', BooleanType::singleton()],
            '4 > 2' => ['4 > 2', BooleanType::singleton()],
            '4 >= 2' => ['4 >= 2', BooleanType::singleton()],
            '4 < 2' => ['4 < 2', BooleanType::singleton()],
            '4 <= 2' => ['4 <= 2', BooleanType::singleton()],
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
        $expressionNode = ASTNodeFixtures::Expression($binaryExpressionAsString);

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
        $expressionNode = ASTNodeFixtures::Expression('true');

        $expectedType = BooleanType::singleton();
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
        $scope = new DummyScope([StringType::singleton()], ['foo' => StringType::singleton()]);
        $expressionTypeResolver = new ExpressionTypeResolver(scope: $scope);
        $expressionNode = ASTNodeFixtures::Expression('foo');

        $expectedType = StringType::singleton();
        $actualType = $expressionTypeResolver->resolveTypeOf($expressionNode);

        $this->assertTrue(
            $expectedType->is($actualType),
            sprintf('Expected %s, got %s', $expectedType::class, $actualType::class)
        );
    }

    /**
     * @return array<string,mixed>
     */
    public static function matchExamples(): array
    {
        return [
            'match (true) { true -> 42 false -> "foo" }' => [
                'match (true) { true -> 42 false -> "foo" }',
                IntegerType::singleton()
            ],
            'match (false) { true -> 42 false -> "foo" }' => [
                'match (false) { true -> 42 false -> "foo" }',
                StringType::singleton()
            ],
            'match (variableOfTypeBoolean) { true -> 42 false -> "foo" }' => [
                'match (variableOfTypeBoolean) { true -> 42 false -> "foo" }',
                UnionType::of(IntegerType::singleton(), StringType::singleton())
            ],
            'match (variableOfTypeBoolean) { true -> variableOfTypeNumber false -> variableOfTypeString }' => [
                'match (variableOfTypeBoolean) { true -> variableOfTypeNumber false -> variableOfTypeString }',
                UnionType::of(IntegerType::singleton(), StringType::singleton())
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
        $scope = new DummyScope(
            [BooleanType::singleton(), StringType::singleton(), IntegerType::singleton()],
            [
                'variableOfTypeBoolean' => BooleanType::singleton(),
                'variableOfTypeString' => StringType::singleton(),
                'variableOfTypeNumber' => IntegerType::singleton()
            ]
        );
        $expressionTypeResolver = new ExpressionTypeResolver(scope: $scope);
        $expressionNode = ASTNodeFixtures::Expression($matchAsString);

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
        $expressionNode = ASTNodeFixtures::Expression('null');

        $expectedType = NullType::singleton();
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
    public function resolvesNumberLiteralToIntegerType(): void
    {
        $scope = new DummyScope();
        $expressionTypeResolver = new ExpressionTypeResolver(scope: $scope);
        $expressionNode = ASTNodeFixtures::Expression('42');

        $expectedType = IntegerType::singleton();
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
        $expressionNode = ASTNodeFixtures::Expression('"foo"');

        $expectedType = StringType::singleton();
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
        $expressionNode = ASTNodeFixtures::Expression('<div></div>');

        $expectedType = StringType::singleton();
        $actualType = $expressionTypeResolver->resolveTypeOf($expressionNode);

        $this->assertTrue(
            $expectedType->is($actualType),
            sprintf('Expected %s, got %s', $expectedType::class, $actualType::class)
        );
    }

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
        $scope = new DummyScope();
        $expressionTypeResolver = new ExpressionTypeResolver(scope: $scope);
        $expressionNode = ASTNodeFixtures::Expression($templateLiteralAsString);

        $expectedType = StringType::singleton();
        $actualType = $expressionTypeResolver->resolveTypeOf($expressionNode);

        $this->assertTrue(
            $expectedType->is($actualType),
            sprintf('Expected %s, got %s', $expectedType::class, $actualType::class)
        );
    }

    /**
     * @return array<string,mixed>
     */
    public static function ternaryOperationExamples(): array
    {
        return [
            'true ? 42 : "foo"' => ['true ? 42 : "foo"', IntegerType::singleton()],
            'false ? 42 : "foo"' => ['false ? 42 : "foo"', StringType::singleton()],
            '1 < 2 ? 42 : "foo"' => ['1 < 2 ? 42 : "foo"', UnionType::of(IntegerType::singleton(), StringType::singleton())]
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
        $expressionNode = ASTNodeFixtures::Expression($ternaryOperationAsString);

        $actualType = $expressionTypeResolver->resolveTypeOf($expressionNode);

        $this->assertTrue(
            $expectedType->is($actualType),
            sprintf('Expected %s, got %s', $expectedType::class, $actualType::class)
        );
    }
}
