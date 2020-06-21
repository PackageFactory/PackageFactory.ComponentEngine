<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Module;

use PackageFactory\ComponentEngine\Loader\LoaderInterface;
use PackageFactory\ComponentEngine\Pragma\AfxPragmaInterface;
use PackageFactory\ComponentEngine\Runtime\AfxEvaluatorInterface;
use PackageFactory\ComponentEngine\Runtime\Context;

final class ExportEvaluator implements AfxEvaluatorInterface
{
    /**
     * @var LoderInterface
     */
    private $loader;

    /**
     * @var Module
     */
    private $module;

    /**
     * @var string
     */
    private $exportName;

    /**
     * @param LoaderInterface $loader
     * @param Module $module
     * @param string $exportName
     */
    private function __construct(
        LoaderInterface $loader,
        Module $module,
        string $exportName
    ) {
        $this->loader = $loader;
        $this->module = $module;
        $this->exportName = $exportName;
    }

    /**
     * @param LoaderInterface $loader
     * @param Module $module
     * @param string $exportName
     * @return self
     */
    public static function createFromLoaderAndModuleAndExportName(
        LoaderInterface $loader,
        Module $module,
        string $exportName
    ): self {
        return new self($loader, $module, $exportName);
    }

    /**
     * @param AfxPragmaInterface $pragma
     * @param Context $context
     * @return mixed
     */
    public function evaluate(AfxPragmaInterface $pragma, Context $context)
    {
        return $this->module->evaluateExport($this->loader, $pragma, $context, $this->exportName);
    }
}