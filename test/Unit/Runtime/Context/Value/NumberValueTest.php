<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\Value\NumberValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\AbstractValueTest;

final class NumberValueTest extends AbstractValueTest
{
    /**
     * @return NumberValue
     */
    public function getValue(): ValueInterface
    {
        return NumberValue::zero();
    }

    /**
     * @test
     * @return void
     */
    public function testIsCastableToString(): void
    {
        $this->assertTrue($this->getValue()->isCastableToString());
    }

    /**
     * @test
     * @return void
     */
    public function testAsStringValue(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @return void
     */
    public function testAsBooleanValue(): void
    {
        $this->markTestIncomplete();
    }
}