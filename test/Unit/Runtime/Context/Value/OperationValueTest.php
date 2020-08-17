<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\Value\OperationValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Library\OperationInterface;
use PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\AbstractValueTest;

final class OperationValueTest extends AbstractValueTest
{
    /**
     * @return OperationValue
     */
    public function getValue(): ValueInterface
    {
        /** @var ValueInterface $value */
        $value = $this->createMock(ValueInterface::class);

        /** @var OperationInterface $operation */
        $operation = $this->createMock(OperationInterface::class);

        return OperationValue::fromValueAndOperation($value, $operation);
    }
}