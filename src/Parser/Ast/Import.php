<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast;

use PackageFactory\ComponentEngine\Parser\Source\Path;

final class Import implements \JsonSerializable
{
    /**
     * @var Path
     */
    private $path;

    private function __construct(Path $path)
    {
        $this->path = $path;
    }

    public static function createFromUriString(string $path): self
    {
        return new self(Path::createFromString($path));
    }

    public function getPath(): Path
    {
        return $this->path;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'Import',
            'properties' => [
                'path' => $this->path
            ]
        ];
    }
}