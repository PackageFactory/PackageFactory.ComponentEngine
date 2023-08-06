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

use PackageFactory\ComponentEngine\Domain\TypeName\TypeName;
use PackageFactory\ComponentEngine\Domain\VariableName\VariableName;
use PackageFactory\ComponentEngine\Test\Unit\Language\ASTNodeFixtures;
use PackageFactory\ComponentEngine\Test\Unit\Module\Loader\Fixtures\DummyLoader;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\TypeSystem\AtomicTypeInterface;
use PackageFactory\ComponentEngine\TypeSystem\Scope\ModuleScope\ModuleScope;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;
use PHPUnit\Framework\TestCase;

final class ModuleScopeTest extends TestCase
{
    private function mockAtomicType(string $name): AtomicTypeInterface
    {
        return new class($name) implements AtomicTypeInterface
        {
            public function __construct(private readonly string $name)
            {
            }

            public function getName(): TypeName
            {
                return TypeName::from($this->name);
            }

            public function is(TypeInterface $other): bool
            {
                return $other === $this;
            }
        };
    }

    /**
     * @test
     * @return void
     */
    public function resolvesTypeReferencesForModuleImports(): void
    {
        $moduleAsString = <<<EOT
        from "./Foo.afx" import { Foo }
        from "./Bar.afx" import { Bar, Baz }

        export struct Qux {}
        EOT;
        $moduleNode = ASTNodeFixtures::Module($moduleAsString);
        $moduleScope = new ModuleScope(
            loader: new DummyLoader([
                './Foo.afx' => [
                    'Foo' => $typeOfFoo = $this->createStub(AtomicTypeInterface::class),
                ],
                './Bar.afx' => [
                    'Bar' => $typeOfBar = $this->createStub(AtomicTypeInterface::class),
                    'Baz' => $typeOfBaz = $this->createStub(AtomicTypeInterface::class)
                ],
            ]),
            moduleNode: $moduleNode,
            parentScope: new DummyScope()
        );

        $this->assertSame(
            $typeOfFoo,
            $moduleScope->getType(TypeName::from('Foo'))
        );

        $this->assertSame(
            $typeOfBar,
            $moduleScope->getType(TypeName::from('Bar'))
        );

        $this->assertSame(
            $typeOfBaz,
            $moduleScope->getType(TypeName::from('Baz'))
        );
    }

    /**
     * @test
     * @return void
     */
    public function fallsBackToParentScopeWhenProvidingTypesForValues(): void
    {
        $moduleNode = ASTNodeFixtures::Module('from "y" import { y } export struct Qux {}');
        $moduleScope = new ModuleScope(
            loader: new DummyLoader(),
            moduleNode: $moduleNode,
            parentScope: new DummyScope(
                [
                    $typeOfFoo = $this->mockAtomicType('Foo')
                ],
                [
                    'foo' => $typeOfFoo
                ]
            )
        );

        $this->assertSame(
            $typeOfFoo,
            $moduleScope->getTypeOf(VariableName::from('foo'))
        );
    }
}
