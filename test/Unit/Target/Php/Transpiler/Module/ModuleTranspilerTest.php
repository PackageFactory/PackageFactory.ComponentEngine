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

namespace PackageFactory\ComponentEngine\Test\Unit\Target\Php\Transpiler\Module;

use PackageFactory\ComponentEngine\Parser\Ast\ModuleNode;
use PackageFactory\ComponentEngine\Test\Unit\Module\Loader\Fixtures\DummyLoader;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\Module\ModuleTranspiler;
use PackageFactory\ComponentEngine\Test\Unit\Language\ASTNodeFixtures;
use PackageFactory\ComponentEngine\TypeSystem\Scope\GlobalScope\GlobalScope;
use PHPUnit\Framework\TestCase;

final class ModuleTranspilerTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function transpilesModuleNodesThatContainComponentDeclarations(): void
    {
        $moduleNodeAsString = <<<EOT
        export component HelloWorld {
            return <p>Hello World</p>
        }
        EOT;
        $moduleTranspiler = new ModuleTranspiler(
            loader: new DummyLoader(),
            globalScope: GlobalScope::singleton(),
            strategy: new ModuleTestStrategy()
        );
        $moduleNode = ASTNodeFixtures::Module($moduleNodeAsString);

        $expectedTranspilationResult = <<<PHP
        <?php

        declare(strict_types=1);

        namespace Vendor\\Project\\Component;

        use Vendor\\Project\\BaseClass;

        final class HelloWorld extends BaseClass
        {
            public function render(): string
            {
                return '<p>Hello World</p>';
            }
        }

        PHP;
        $actualTranspilationResult = $moduleTranspiler->transpile(
            $moduleNode
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
    public function transpilesModuleNodesThatContainEnumDeclarations(): void
    {
        $moduleNodeAsString = <<<EOT
        export enum SomeEnum {
            A
            B
            C
        }
        EOT;
        $moduleTranspiler = new ModuleTranspiler(
            loader: new DummyLoader(),
            globalScope: GlobalScope::singleton(),
            strategy: new ModuleTestStrategy()
        );
        $moduleNode = ASTNodeFixtures::Module($moduleNodeAsString);

        $expectedTranspilationResult = <<<PHP
        <?php

        declare(strict_types=1);

        namespace Vendor\\Project\\Component;

        enum SomeEnum : string
        {
            case A = 'A';
            case B = 'B';
            case C = 'C';
        }

        PHP;
        $actualTranspilationResult = $moduleTranspiler->transpile(
            $moduleNode
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
    public function transpilesModuleNodesThatContainStructDeclarations(): void
    {
        $moduleNodeAsString = <<<EOT
        export struct SomeStruct {
            foo: string
            bar: number
            baz: boolean
        }
        EOT;
        $moduleTranspiler = new ModuleTranspiler(
            loader: new DummyLoader(),
            globalScope: GlobalScope::singleton(),
            strategy: new ModuleTestStrategy()
        );
        $moduleNode = ASTNodeFixtures::Module($moduleNodeAsString);

        $expectedTranspilationResult = <<<'PHP'
        <?php

        declare(strict_types=1);

        namespace Vendor\Project\Component;

        use Vendor\Project\BaseClass;

        final class SomeStruct extends BaseClass
        {
            public function __construct(
                public readonly string $foo,
                public readonly int|float $bar,
                public readonly bool $baz
            ) {
            }
        }

        PHP;
        $actualTranspilationResult = $moduleTranspiler->transpile(
            $moduleNode
        );

        $this->assertEquals(
            $expectedTranspilationResult,
            $actualTranspilationResult
        );
    }
}
