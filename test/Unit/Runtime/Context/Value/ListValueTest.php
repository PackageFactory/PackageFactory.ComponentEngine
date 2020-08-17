<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\Value\ListValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\AbstractValueTest;

final class ListValueTest extends AbstractValueTest
{
    /**
     * @return ListValue
     */
    public function getValue(): ValueInterface
    {
        return ListValue::fromArray([]);
    }

    /**
     * @test
     * @return void
     */
    public function testIsCountable(): void
    {
        $this->assertTrue($this->getValue()->isCountable());
    }

    /**
     * @test
     * @return void
     */
    public function testAsBooleanValue(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @return void
     */
    public function testAsIterable(): void
    {
        $this->markTestIncomplete();
    }
}