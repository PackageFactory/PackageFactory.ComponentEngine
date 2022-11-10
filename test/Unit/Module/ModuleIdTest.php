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

namespace PackageFactory\ComponentEngine\Test\Unit\Module;

use PackageFactory\ComponentEngine\Module\ModuleId;
use PackageFactory\ComponentEngine\Parser\Source\Path;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PHPUnit\Framework\TestCase;

final class ModuleIdTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function canBeCreatedFromString(): void
    {
        $moduleId = ModuleId::fromString('module-id');
        $this->assertInstanceOf(ModuleId::class, $moduleId);
    }

    /**
     * @test
     * @return void
     */
    public function isFlyweight(): void
    {
        $moduleId1 = ModuleId::fromString('module-id');
        $moduleId2 = ModuleId::fromString('module-id');

        $this->assertSame($moduleId1, $moduleId2);
    }

    /**
     * @test
     * @return void
     */
    public function canBeConvertedToString(): void
    {
        $moduleId = ModuleId::fromString('module-id');
        $this->assertEquals('module-id', (string) $moduleId);
    }

    /**
     * @test
     * @return void
     */
    public function canBeCreatedFromSourceWithAbsolutePath(): void
    {
        $source = new Source(
            Path::fromString('/some/path/to/module'),
            '...'
        );
        $moduleId = ModuleId::fromSource($source);

        $this->assertInstanceOf(ModuleId::class, $moduleId);
        $this->assertEquals('/some/path/to/module', (string) $moduleId);
    }

    /**
     * @test
     * @return void
     */
    public function canBeCreatedFromSourceWithRelativePath(): void
    {
        $source = new Source(
            Path::fromString('./some/relative/path/to/module'),
            '...'
        );
        $moduleId = ModuleId::fromSource($source);

        $this->assertInstanceOf(ModuleId::class, $moduleId);
        $this->assertEquals('./some/relative/path/to/module', (string) $moduleId);
    }

    /**
     * @test
     * @return void
     */
    public function canBeCreatedFromSourceWithMemoryPath(): void
    {
        $source = Source::fromString('...');
        $moduleId = ModuleId::fromSource($source);

        $this->assertInstanceOf(ModuleId::class, $moduleId);
        $this->assertEquals(':memory:#ab5df625bc76dbd4e163bed2dd888df828f90159bb93556525c31821b6541d46', (string) $moduleId);
    }

    /**
     * @test
     * @return void
     */
    public function isFlyweightWhenCreatedFromSourceWithMemoryPath(): void
    {
        $source1 = Source::fromString('...');
        $moduleId1 = ModuleId::fromSource($source1);
        $source2 = Source::fromString('...');
        $moduleId2 = ModuleId::fromSource($source2);

        $this->assertSame($moduleId1, $moduleId2);
    }

    /**
     * @test
     * @return void
     */
    public function creationFromDifferntSourcesWithMemoryPathsLeadsToDifferentInstances(): void
    {
        $source1 = Source::fromString('source 1');
        $moduleId1 = ModuleId::fromSource($source1);
        $source2 = Source::fromString('source 2');
        $moduleId2 = ModuleId::fromSource($source2);

        $this->assertNotSame($moduleId1, $moduleId2);
    }
}