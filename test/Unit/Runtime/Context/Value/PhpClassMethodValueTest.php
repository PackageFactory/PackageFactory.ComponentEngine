<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\Value\PhpClassMethodValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\AbstractValueTest;

final class PhpClassMethodValueTest extends AbstractValueTest
{
    /**
     * @return PhpClassMethodValue
     */
    public function getValue(): ValueInterface
    {
        $object = new class {
            public function method() {
                return null;
            }
        };

        return PhpClassMethodValue::fromObjectAndMethodName($object, 'method');
    }
}