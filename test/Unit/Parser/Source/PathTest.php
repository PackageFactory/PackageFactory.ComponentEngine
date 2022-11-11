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

namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Source;

use PackageFactory\ComponentEngine\Parser\Source\Path;
use PHPUnit\Framework\TestCase;

final class PathTest extends TestCase
{
    public function relatedPathExamples(): array
    {
        return [
            '(UNIX) Two unrelated, absolute paths' => [
                '/some/where/in/the/filesystem',
                '/else/where/in/the/filesystem',
                '/else/where/in/the/filesystem',
            ],
            '(UNIX) Two related, absolute paths' => [
                '/some/where/in/the/filesystem',
                '/some/where/in/the/filesystem/deeply/deeply/hidden',
                '/some/where/in/the/filesystem/deeply/deeply/hidden',
            ],
            '(UNIX) First path relative, other path absolute' => [
                './foo/bar',
                '/some/where/in/the/filesystem',
                '/some/where/in/the/filesystem',
            ],
            '(UNIX) First path absolute, other path relative' => [
                '/some/where/in/the/filesystem',
                './foo/bar',
                '/some/where/in/the/foo/bar',
            ],
            '(Windows) Two unrelated, absolute paths' => [
                'c:\\some\\where\\in\\the\\filesystem',
                'c:\\else\\where\\in\\the\\filesystem',
                'c:/else/where/in/the/filesystem',
            ],
            '(Windows) Two related, absolute paths' => [
                'C:\\some\\where\\in\\the\\filesystem',
                'C:\\some\\where\\in\\the\\filesystem\\deeply\\deeply\\hidden',
                'C:/some/where/in/the/filesystem/deeply/deeply/hidden',
            ],
            '(Windows) First path relative, other path absolute' => [
                './foo/bar',
                'd:\\some\\where\\in\\the\\filesystem',
                'd:/some/where/in/the/filesystem',
            ],
            '(Windows) First path absolute, other path relative' => [
                'E:\\some\\where\\in\\the\\filesystem',
                './foo/bar',
                'E:/some/where/in/the/foo/bar',
            ],
        ];
    }

    /**
     * @dataProvider relatedPathExamples
     * @test
     * @param string $pathAsString
     * @param string $otherPathAsString
     * @param string $expectedResultingPathAsString
     * @return void
     */
    public function resolvesRelationToOtherPath(string $pathAsString, string $otherPathAsString, string $expectedResultingPathAsString): void
    {
        $path = Path::fromString($pathAsString);
        $otherPath = Path::fromString($otherPathAsString);

        $this->assertEquals(
            $expectedResultingPathAsString,
            $path->resolveRelationTo($otherPath)->value
        );
    }
}