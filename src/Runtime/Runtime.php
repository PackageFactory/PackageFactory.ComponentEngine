<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime;

use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Loader\LoaderInterface;
use PackageFactory\ComponentEngine\Runtime\Loader\RootLoader;

final class Runtime
{
    /**
     * @var ValueInterface<mixed>
     */
    private $context;

    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var Library
     */
    private $library;

    /**
     * @param ValueInterface<mixed> $context
     * @param LoaderInterface $loader
     * @param Library $library
     */
    private function __construct(
        ValueInterface $context, 
        LoaderInterface $loader,
        Library $library
    ) {
        $this->context = $context;
        $this->loader = $loader;
        $this->library = $library;
    }

    /**
     * @return self
     */
    public static function default(): self
    {
        return new self(Context::empty(), RootLoader::fromConfiguration([]), Library::default());
    }

    /**
     * @return ValueInterface<mixed>
     */
    public function getContext(): ValueInterface
    {
        return $this->context;
    }

    /**
     * @param ValueInterface<mixed> $context
     * @return self
     */
    public function withContext(ValueInterface $context): self
    {
        return new self($context, $this->loader, $this->library);
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
        return new self($this->context, $loader, $this->library);
    }

    /**
     * @return Library
     */
    public function getLibrary(): Library
    {
        return $this->library;
    }

    /**
     * @param Library $library
     * @return self
     */
    public function withLibrary(Library $library): self
    {
        return new self($this->context, $this->loader, $library);
    }
}