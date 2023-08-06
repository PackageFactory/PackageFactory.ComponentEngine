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

namespace PackageFactory\ComponentEngine\Test\Unit\Target\Php\Transpiler\ValueReference;

use PackageFactory\ComponentEngine\Module\ModuleId;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\ValueReference\ValueReferenceTranspiler;
use PackageFactory\ComponentEngine\Test\Unit\Language\ASTNodeFixtures;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumStaticType;
use PHPUnit\Framework\TestCase;

final class ValueReferenceTranspilerTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function transpilesValueReferenceNodes(): void
    {
        $identifierTranspiler = new ValueReferenceTranspiler(
            scope: new DummyScope()
        );
        $identifierNode = ASTNodeFixtures::ValueReference('foo');

        $expectedTranspilationResult = '$this->foo';
        $actualTranspilationResult = $identifierTranspiler->transpile(
            $identifierNode
        );

        $this->assertEquals(
            $expectedTranspilationResult,
            $actualTranspilationResult
        );
    }

    /**
     * @test
     * @return void
     */
    public function transpilesValueReferenceNodesReferringToEnums(): void
    {
        $identifierTranspiler = new ValueReferenceTranspiler(
            scope: new DummyScope(
                [
                    $someEnumType = EnumStaticType::fromModuleIdAndDeclaration(
                        ModuleId::fromString("module-a"),
                        ASTNodeFixtures::EnumDeclaration(
                            'enum SomeEnum { A B C }'
                        )
                    )
                ],
                ['SomeEnum' => $someEnumType]
            )
        );
        $identifierNode = ASTNodeFixtures::ValueReference('SomeEnum');

        $expectedTranspilationResult = 'SomeEnum';
        $actualTranspilationResult = $identifierTranspiler->transpile(
            $identifierNode
        );

        $this->assertEquals(
            $expectedTranspilationResult,
            $actualTranspilationResult
        );
    }
}
