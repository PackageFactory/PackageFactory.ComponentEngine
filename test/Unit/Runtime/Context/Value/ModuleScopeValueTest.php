<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\Value;

use PackageFactory\ComponentEngine\Parser\Ast\Module\Module;
use PackageFactory\ComponentEngine\Runtime\Context\Value\ModuleScopeValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\AbstractValueTest;

final class ModuleScopeValueTest extends AbstractValueTest
{
    /**
     * @return ModuleScopeValue
     */
    public function getValue(): ValueInterface
    {
        /** @var Module $module */
        $module = $this->createMock(Module::class);
        return ModuleScopeValue::fromModule($module);
    }
}