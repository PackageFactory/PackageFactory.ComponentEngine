<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Loader;

use PackageFactory\ComponentEngine\Parser\Ast\Module;
use PackageFactory\ComponentEngine\Parser\Source\Path;

final class RootLoader implements LoaderInterface
{
    /**
     * @var array<string, LoaderInterface>
     */
    private $loaders;

    /**
     * @param array<string, LoaderInterface> $loaders
     */
    private function __construct(array $loaders)
    {
        $this->loaders = $loaders;

        if (!isset($this->loaders['/\\.md$/'])) {
            $this->loaders['/\\.md$/'] = new ComponentLoader();
        }
    }

    /**
     * @param array<string, LoaderInterface> $configuration
     * @return self
     */
    public static function createFromConfiguration(array $configuration): self
    {
        return new self($configuration);
    }

    public function load(Path $path): Module
    {
        foreach ($this->loaders as $regex => $loader) {
            if (preg_match($regex, (string) $path) === 1) {
                return $loader->load($path);
            }
        }

        throw new \Exception('@TODO: Could not find loader for ' . $path);
    }
}