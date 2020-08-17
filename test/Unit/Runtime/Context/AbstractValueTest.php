<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context;

use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractValueTest extends TestCase
{
    /**
     * @return ValueInterface<mixed>
     */
    abstract public function getValue(): ValueInterface;

    /**
     * @test
     * @return void
     */
    public function testIsCountable(): void
    {
        $this->assertFalse($this->getValue()->isCountable());
    }

    /**
     * @test
     * @return void
     */
    public function testIsCastableToString(): void
    {
        $this->assertFalse($this->getValue()->isCastableToString());
    }

    /**
     * @test
     * @return void
     */
    public function testAsStringValue(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->getValue()->asStringValue();
    }

    /**
     * @test
     * @return void
     */
    public function testAsBooleanValue(): void
    {
        $this->assertTrue($this->getValue()->asBooleanValue());
    }

    /**
     * @test
     * @return void
     */
    public function testAsIterable(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->getValue()->asIterable();
    }

    /**
     * @test
     * @return void
     */
    public function testGet(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @return void
     */
    public function testMerge(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @return void
     */
    public function testCall(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @return void
     */
    public function testGreaterThan(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->getValue()->greaterThan($this->getValue());
    }

    /**
     * @test
     * @return void
     */
    public function testLessThan(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->getValue()->lessThan($this->getValue());
    }

    /**
     * @test
     * @return void
     */
    public function testEquals(): void
    {
        $value = $this->getValue();
        $this->assertTrue($value->equals($value));
    }

    /**
     * @test
     * @return void
     */
    public function testAdd(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->getValue()->add($this->getValue());
    }

    /**
     * @test
     * @return void
     */
    public function testSubtract(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->getValue()->subtract($this->getValue());
    }

    /**
     * @test
     * @return void
     */
    public function testMultiply(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->getValue()->multiply($this->getValue());
    }

    /**
     * @test
     * @return void
     */
    public function testDivide(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->getValue()->divide($this->getValue());
    }

    /**
     * @test
     * @return void
     */
    public function testModulo(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->getValue()->modulo($this->getValue());
    }

    /**
     * @test
     * @return void
     */
    public function testGetValue(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @return void
     */
    public function testGetDebugName(): void
    {
        $this->markTestIncomplete();
    }
}