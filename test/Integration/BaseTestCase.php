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

namespace PackageFactory\ComponentEngine\Test\Integration;

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

abstract class BaseTestCase extends TestCase
{
    use MatchesSnapshots;

    /**
     * @param string $type
     * @param string $scope
     * @return iterable<string, array<int, string>>
     */
    public function fixtures(string $type, ?string $scope = ''): iterable
    {
        if ($filename = (new \ReflectionClass($this))->getFileName()) {
            $fixtures = new \DirectoryIterator(
                dirname($filename) .
                    ($scope ? DIRECTORY_SEPARATOR : '') .
                    $scope .
                    DIRECTORY_SEPARATOR .
                    'fixtures' .
                    DIRECTORY_SEPARATOR .
                    $type
            );

            foreach ($fixtures as $fixture) {
                if (!$fixture->isDir()) {
                    $key = $type . ' > ' . $fixture->getFilename();
                    yield $key => [str_replace((string) getcwd(), '.', $fixture->getPathName())];
                }
            }
        }
    }

    protected function getSnapshotDirectory(): string
    {
        if ($filename = (new \ReflectionClass($this))->getFileName()) {
            return dirname($filename) . DIRECTORY_SEPARATOR . 'snapshots';
        } else {
            return __DIR__ . DIRECTORY_SEPARATOR . 'snapshots';
        }
    }
}
