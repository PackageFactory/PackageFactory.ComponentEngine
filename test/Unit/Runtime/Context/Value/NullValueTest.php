<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\Value\NullValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\AbstractValueTest;

final class NullValueTest extends AbstractValueTest
{
    /**
     * @return NullValue
     */
    public function getValue(): ValueInterface
    {
        return NullValue::create();
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