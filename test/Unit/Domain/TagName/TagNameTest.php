<?php

/**
 * PackageFactory.ComponentEngine - Universal View Components for PHP
 *   Copyright (C) 2023 Contributors of PackageFactory.ComponentEngine
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

namespace PackageFactory\ComponentEngine\Test\Unit\Domain\TagName;

use PackageFactory\ComponentEngine\Domain\TagName\TagName;
use PHPUnit\Framework\TestCase;

final class TagNameTest extends TestCase
{
    /**
     * @test
     */
    public function isFlyweight(): void
    {
        $this->assertSame(TagName::from('div'), TagName::from('div'));
        $this->assertSame(TagName::from('a'), TagName::from('a'));
        $this->assertSame(TagName::from('vendor-component'), TagName::from('vendor-component'));

        $this->assertNotSame(TagName::from('pre'), TagName::from('table'));
    }
}
