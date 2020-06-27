<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Loader;

use PackageFactory\ComponentEngine\Parser\Source\Path;
use PackageFactory\ComponentEngine\Parser\Ast\Module\Module;

interface LoaderInterface
{
    /**
     * @param Path $path
     * @return Module
     */
    public function load(Path $path): Module;
}