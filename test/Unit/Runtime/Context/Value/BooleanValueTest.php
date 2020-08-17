<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\Value\BooleanValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\AbstractValueTest;

final class BooleanValueTest extends AbstractValueTest
{
    /**
     * @return BooleanValue
     */
    public function getValue(): ValueInterface
    {
        return BooleanValue::fromBoolean(true);
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