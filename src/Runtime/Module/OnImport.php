<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Module;

use PackageFactory\ComponentEngine\Parser\Ast\Module\Import;
use PackageFactory\ComponentEngine\Parser\Ast\Module\Module;
use PackageFactory\ComponentEngine\Parser\Source\Path;
use PackageFactory\ComponentEngine\Runtime\Context;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class onImport
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
            Path::createFromString($import->getTarget())
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
