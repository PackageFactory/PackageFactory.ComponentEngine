<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\Value\AfxValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\AbstractValueTest;
use PackageFactory\VirtualDOM\Model\ComponentInterface;

final class AfxValueTest extends AbstractValueTest
{
    /**
     * @return AfxValue
     */
    public function getValue(): ValueInterface
    {
        /** @var ComponentInterface $component */
        $component = $this->createMock(ComponentInterface::class);

        return AfxValue::fromComponent($component);
    }
}