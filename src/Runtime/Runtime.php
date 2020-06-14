<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime;

use PackageFactory\ComponentEngine\Loader\LoaderInterface;
use PackageFactory\ComponentEngine\Loader\RootLoader;
use PackageFactory\ComponentEngine\Parser\Ast\Module;
use PackageFactory\ComponentEngine\Parser\Ast\Tag;
use PackageFactory\VirtualDOM;

final class Runtime
{
    /**
     * @var Module
     */
    private $rootModule;

    /**
     * @var LoaderInterface
     */
    private $rootLoader;

    private function __construct(Module $rootModule, LoaderInterface $rootLoader)
    {
        $this->rootModule = $rootModule;
        $this->rootLoader = $rootLoader;
    }

    public static function createFromModule(Module $module): self
    {
        return new self($module, RootLoader::createFromConfiguration([]));
    }

    public function evaluate(Context $context): ?VirtualDOM\Node
    {
        foreach ($this->rootModule->getImports() as $name => $import) {
            $issuerPath = $this->rootModule->getSource()->getPath();
            $importPath = $import->getPath();
            $loadPath = $issuerPath->getRelativePathTo($importPath);
            $importedModule = $this->rootLoader->load($loadPath);

            if ($importedModule->getExport() instanceof Tag) {
                $context = $context->withMergedProperties([
                    $name => new self($importedModule, $this->rootLoader)
                ]);
            }
            else {
                $context = $context->withMergedProperties([
                    $name => $importedModule->getExport()
                ]);
            }
        }
        $export = $this->rootModule->getExport();

        if ($export instanceof Tag) {
            return Evaluate\Tag::evaluate($context, $export);
        } 
        else {
            throw new \RuntimeException('@TODO: Cannot evaluate module');
        }
    }
}