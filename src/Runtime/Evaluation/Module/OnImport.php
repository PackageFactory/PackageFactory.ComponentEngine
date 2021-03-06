<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Module;

use PackageFactory\ComponentEngine\Parser\Ast\Module\Import;
use PackageFactory\ComponentEngine\Parser\Ast\Module\Module;
use PackageFactory\ComponentEngine\Parser\Source\Path;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnImport
{
    /**
     * @param Runtime $runtime
     * @param Module $root
     * @param Import $import
     * @return ValueInterface<mixed>
     */
    public static function evaluate(Runtime $runtime, Module $root, Import $import): ValueInterface
    {
        $module = $runtime->getLoader()->load($root->getSource()->getPath()->getRelativePathTo(
            Path::fromString($import->getTarget())
        ));

        return OnModule::evaluate($runtime, $module, $import->getForeignName());
    }
}
