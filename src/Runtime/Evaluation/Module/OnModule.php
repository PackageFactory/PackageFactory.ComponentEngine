<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Module;

use PackageFactory\ComponentEngine\Parser\Ast\Module\Module;
use PackageFactory\ComponentEngine\Runtime\Context\Value\ModuleScopeValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnModule
{
    /**
     * @param Runtime $runtime
     * @param Module $module
     * @param string $exportName
     * @return ValueInterface<mixed>
     */
    public static function evaluate(Runtime $runtime, Module $module, string $exportName = 'default'): ValueInterface 
    {
        $export = $module->getExport($exportName);
        $context = ModuleScopeValue::fromModule($module);
        $runtime = $context->bindRuntime(
            $runtime->withContext(
                $context->merge($runtime->getContext())
            )
        );

        return OnExport::evaluate($runtime, $export);
    }


}
