<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Module;

use PackageFactory\ComponentEngine\Parser\Ast\Module\Import;
use PackageFactory\ComponentEngine\Parser\Ast\Module\Module;
use PackageFactory\ComponentEngine\Parser\Source\Path;
use PackageFactory\ComponentEngine\Runtime\Context;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnImport
{
    /**
     * @param Runtime $runtime
     * @param Module $root
     * @param Import $import
     * @return \Closure
     */
    public static function evaluate(Runtime $runtime, Module $root, Import $import) 
    {
        $module = $runtime->getLoader()->load($root->getSource()->getPath()->getRelativePathTo(
            Path::fromString($import->getTarget())
        ));
    
        return function(Context $context) use ($runtime, $module, $import) {
            return OnModule::evaluate(
                $runtime->withContext($context),
                $module,
                $import->getForeignName()
            );
        };
    }
}
