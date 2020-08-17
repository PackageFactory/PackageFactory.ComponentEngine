<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\Value\IteratorValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\AbstractValueTest;

final class IteratorValueTest extends AbstractValueTest
{
    /**
     * @return IteratorValue
     */
    public function getValue(): ValueInterface
    {
        /** @var \Iterator $iterator */
        $iterator = $this->createMock(\Iterator::class);
        return IteratorValue::fromIterator($iterator);
    }

    /**
     * IteratorValues should be countable, if the subject \Iterator 
     * implements the \Countable interface.
     * 
     * @test
     * @return void
     */
    public function testIsCountable(): void
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

    /**
     * @test
     * @return void
     */
    public function testAsIterable(): void
    {
        $this->markTestIncomplete();
    }
}