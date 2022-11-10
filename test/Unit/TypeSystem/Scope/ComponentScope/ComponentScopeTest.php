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

namespace PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\ComponentScope;

use PackageFactory\ComponentEngine\Parser\Ast\ComponentDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\TypeReferenceNode;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\TypeSystem\Scope\ComponentScope\ComponentScope;
use PackageFactory\ComponentEngine\TypeSystem\Scope\GlobalScope\GlobalScope;
use PackageFactory\ComponentEngine\TypeSystem\Type\NumberType\NumberType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PHPUnit\Framework\TestCase;

final class ComponentScopeTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function providesTheTypesOfComponentApiMembers(): void
    {
        $componentDeclarationAsString = <<<EOT
        component Foo {
            foo: string

            return <div>{foo}</div>
        }
        EOT;
        $componentDeclarationNode = ComponentDeclarationNode::fromString($componentDeclarationAsString);
        $componentScope = new ComponentScope(
            componentDeclarationNode: $componentDeclarationNode,
            parentScope: GlobalScope::get()
        );

        $expectedType = StringType::get();
        $actualType = $componentScope->lookupTypeFor('foo');

        $this->assertNotNull($actualType);

        $this->assertTrue(
            $expectedType->is($actualType),
            sprintf('Expected %s, got %s', $expectedType::class, $actualType::class)
        );
    }

    /**
     * @test
     * @return void
     */
    public function fallsBackToParentScope(): void
    {
        $componentDeclarationAsString = <<<EOT
        component Foo {
            foo: string

            return <div>{foo}</div>
        }
        EOT;
        $componentDeclarationNode = ComponentDeclarationNode::fromString($componentDeclarationAsString);
        $componentScope = new ComponentScope(
            componentDeclarationNode: $componentDeclarationNode,
            parentScope: new DummyScope(['bar' => NumberType::get()], [])
        );

        $expectedType = NumberType::get();
        $actualType = $componentScope->lookupTypeFor('bar');

        $this->assertNotNull($actualType);

        $this->assertTrue(
            $expectedType->is($actualType),
            sprintf('Expected %s, got %s', $expectedType::class, $actualType::class)
        );
    }

    /**
     * @test
     * @return void
     */
    public function resolvesTypeReferencesUsingParentScope(): void
    {
        $componentDeclarationAsString = <<<EOT
        component Foo {
            foo: string

            return <div>{foo}</div>
        }
        EOT;
        $componentDeclarationNode = ComponentDeclarationNode::fromString($componentDeclarationAsString);
        $componentScope = new ComponentScope(
            componentDeclarationNode: $componentDeclarationNode,
            parentScope: new DummyScope([], ['StringAlias' => StringType::get()])
        );

        $expectedType = StringType::get();
        $actualType = $componentScope->resolveTypeReference(
            TypeReferenceNode::fromString('StringAlias')
        );

        $this->assertTrue(
            $expectedType->is($actualType),
            sprintf('Expected %s, got %s', $expectedType::class, $actualType::class)
        );
    }
}