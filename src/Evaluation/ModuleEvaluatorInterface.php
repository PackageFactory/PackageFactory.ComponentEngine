<?php
namespace PackageFactory\ComponentEngine\Evaluation;

use PackageFactory\ComponentEngine\Parser\Ast\Module\Module;

/**
 * @template T
 */
interface ModuleEvaluatorInterface
{
    /**
     * @param Module $module
     * @return T
     */
    public function evaluate(Module $module);

    /**
     * @param Module $module
     * @param string $export
     * @return T
     */
    public function evaluateExport(Module $module, string $export);
}