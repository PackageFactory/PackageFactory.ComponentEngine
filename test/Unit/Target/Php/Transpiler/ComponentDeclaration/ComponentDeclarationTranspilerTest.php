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

namespace PackageFactory\ComponentEngine\Test\Unit\Target\Php\Transpiler\ComponentDeclaration;

use PackageFactory\ComponentEngine\Parser\Ast\ComponentDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\ModuleNode;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\ComponentDeclaration\ComponentDeclarationTranspiler;
use PackageFactory\ComponentEngine\TypeSystem\Scope\GlobalScope\GlobalScope;
use PHPUnit\Framework\TestCase;

final class ComponentDeclarationTranspilerTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function transpilesComponentDeclarationNodes(): void
    {
        $componentModuleAsString = <<<EOT
        export component Greeter {
            name: string

            return <h1>Hello, {name}</h1>
        }
        EOT;
        $moduleNode = ModuleNode::fromString($componentModuleAsString);
        $componentDeclarationTranspiler = new ComponentDeclarationTranspiler(
            scope: GlobalScope::get(),
            module: $moduleNode,
            strategy: new ComponentDeclarationTestStrategy()
        );
        $componentDeclarationNode = $moduleNode->exports->get('Greeter')?->declaration;
        assert($componentDeclarationNode instanceof ComponentDeclarationNode);

        $expectedTranspilationResult = <<<PHP
        <?php

        declare(strict_types=1);

        namespace Vendor\\Project\\Component;

        use Vendor\\Project\\BaseClass;
        
        final class Greeter extends BaseClass
        {
            public function __construct(
                public readonly string \$name
            ) {
            }

            public function render(): string
            {
                return '<h1>Hello, ' . \$this->name . '</h1>';
            }
        }

        PHP;

        $actualTranspilationResult = $componentDeclarationTranspiler->transpile(
            $componentDeclarationNode
        );

        $this->assertEquals(
            $expectedTranspilationResult,
            $actualTranspilationResult
        );
    }
}