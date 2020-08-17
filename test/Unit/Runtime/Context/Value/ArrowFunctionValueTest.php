<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\Value\ArrowFunctionValue;
use PackageFactory\ComponentEngine\Runtime\Context\Value\NullValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\AbstractValueTest;

final class ArrowFunctionValueTest extends AbstractValueTest
{
    /**
     * @return ArrowFunctionValue
     */
    public function getValue(): ValueInterface
    {
        return ArrowFunctionValue::fromClosure(function () {
            return NullValue::create();
        });
    }
}