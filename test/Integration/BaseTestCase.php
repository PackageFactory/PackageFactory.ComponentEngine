<?php declare(strict_types=1);
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
        }
        else {
            return __DIR__ . DIRECTORY_SEPARATOR . 'snapshots';
        }
    }
}