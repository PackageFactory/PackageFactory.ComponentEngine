<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\Value\StringValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\AbstractValueTest;

final class StringValueTest extends AbstractValueTest
{
    /**
     * @return StringValue
     */
    public function getValue(): ValueInterface
    {
        return StringValue::fromString('');
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
        $value = $this->getValue();
        $this->assertSame($value, $value->asStringValue());
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