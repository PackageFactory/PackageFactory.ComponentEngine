<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Source;

/**
 * @implements \IteratorAggregate<mixed, Fragment>
 */
final class Source implements \IteratorAggregate
{
    /**
     * @var Path
     */
    private $path;

    /**
     * @var string
     */
    private $contents;

    /**
     * @param Path $path
     * @param string $contents
     */
    private function __construct(
        Path $path,
        string $contents
    ) {
        $this->path = $path;
        $this->contents = $contents;
    }

    /**
     * @param string $contents
     * @return Source
     */
    public static function fromString(string $contents): Source
    {
        return new Source(Path::createMemory(), $contents);
    }

    /**
     * @param string $filename
     * @return Source
     */
    public static function fromFile(string $filename): Source
    {
        if ($contents = file_get_contents($filename)) {
            return new Source(Path::fromString($filename), $contents);
        }

        throw new \Exception('@TODO: Could not load file');
    }

    /**
     * @return Path
     */
    public function getPath(): Path
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getContents(): string
    {
        return $this->contents;
    }

    /**
     * @param Source $other
     * @return bool
     */
    public function equals(Source $other): bool
    {
        return $this->contents === $other->getContents();
    }

    /**
     * @return \Iterator<Fragment>
     */
    public function getIterator(): \Iterator
    {
        $rowIndex = 0;
        $columnIndex = 0;
        $length = strlen($this->contents);

        for ($index = 0; $index < $length; $index++) {
            $character = $this->contents[$index];

            yield Fragment::create(
                $character,
                Position::create($index, $rowIndex, $columnIndex),
                Position::create($index, $rowIndex, $columnIndex),
                $this
            );

            if ($character === "\n") {
                $rowIndex++;
                $columnIndex = 0;
            } else {
                $columnIndex++;
            }
        }
    }
}