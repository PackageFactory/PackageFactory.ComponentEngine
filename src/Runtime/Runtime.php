<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime;

use PackageFactory\ComponentEngine\Runtime\Loader\LoaderInterface;
use PackageFactory\ComponentEngine\Runtime\Loader\RootLoader;

final class Runtime
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @param Context $context
     */
    private function __construct(Context $context, LoaderInterface $loader)
    {
        $this->context = $context;
        $this->loader = $loader;
    }

    /**
     * @return self
     */
    public static function default(): self
    {
        return new self(Context::createEmpty(), RootLoader::fromConfiguration([]));
    }

    /**
     * @return Context
     */
    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * @param Context $context
     * @return self
     */
    public function withContext(Context $context): self
    {
        return new self($context, $this->loader);
    }

    /**
     * @return LoaderInterface
     */
    public function getLoader(): LoaderInterface
    {
        return $this->loader;
    }

    /**
     * @param LoaderInterface $loader
     * @return self
     */
    public function withLoader(LoaderInterface $loader): self
    {
        return new self($this->context, $loader);
    }
}