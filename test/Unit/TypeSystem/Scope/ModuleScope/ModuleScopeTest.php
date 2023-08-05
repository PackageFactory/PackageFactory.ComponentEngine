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

namespace PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\ModuleScope;

use PackageFactory\ComponentEngine\Parser\Ast\ModuleNode;
use PackageFactory\ComponentEngine\Parser\Ast\TypeReferenceNode;
use PackageFactory\ComponentEngine\Test\Unit\Module\Loader\Fixtures\DummyLoader;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\TypeSystem\Scope\ModuleScope\ModuleScope;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;
use PHPUnit\Framework\TestCase;

final class ModuleScopeTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function resolvesTypeReferencesForModuleImports(): void
    {
        $moduleAsString = <<<EOT
        from "./Foo.afx" import { Foo }
        from "./Bar.afx" import { Bar, Baz }
        EOT;
        $moduleNode = ModuleNode::fromString($moduleAsString);
        $moduleScope = new ModuleScope(
            loader: new DummyLoader([
                './Foo.afx' => [
                    'Foo' => $typeOfFoo = $this->createStub(TypeInterface::class),
                ],
                './Bar.afx' => [
                    'Bar' => $typeOfBar = $this->createStub(TypeInterface::class),
                    'Baz' => $typeOfBaz = $this->createStub(TypeInterface::class)
                ],
            ]),
            moduleNode: $moduleNode,
            parentScope: new DummyScope()
        );

        $this->assertSame(
            $typeOfFoo,
            $moduleScope->resolveTypeReference(
                TypeReferenceNode::fromString('Foo')
            )
        );

        $this->assertSame(
            $typeOfBar,
            $moduleScope->resolveTypeReference(
                TypeReferenceNode::fromString('Bar')
            )
        );

        $this->assertSame(
            $typeOfBaz,
            $moduleScope->resolveTypeReference(
                TypeReferenceNode::fromString('Baz')
            )
        );
    }

    /**
     * @test
     * @return void
     */
    public function fallsBackToParentScopeWhenProvidingTypesForValues(): void
    {
        $moduleNode = ModuleNode::fromString('from "y" import { y }');
        $moduleScope = new ModuleScope(
            loader: new DummyLoader(),
            moduleNode: $moduleNode,
            parentScope: new DummyScope([
                'foo' => $typeOfFoo = $this->createStub(TypeInterface::class),
            ])
        );

        $this->assertSame(
            $typeOfFoo,
            $moduleScope->lookupTypeFor('foo')
        );
    }
}
