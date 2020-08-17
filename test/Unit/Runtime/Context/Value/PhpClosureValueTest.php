<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\Value\PhpClosureValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\AbstractValueTest;

final class PhpClosureValueTest extends AbstractValueTest
{
    /**
     * @return PhpClosureValue
     */
    public function getValue(): ValueInterface
    {
        return PhpClosureValue::fromClosure(function() {
            return null;
        });
    }
}