<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\Value\PhpClassInstanceValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Context\AbstractValueTest;

final class PhpClassInstanceValueTest extends AbstractValueTest
{
    /**
     * @return PhpClassInstanceValue
     */
    public function getValue(): ValueInterface
    {
        return PhpClassInstanceValue::fromObject(new \stdClass);
    }

    /**
     * PhpClassInstanceValues should be countable, if the subject PHP class
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
     * PhpClassInstanceValues should be string-castable, if the subject PHP class
     * implements a __toString method.
     * 
     * @test
     * @return void
     */
    public function testIsCastableToString(): void
    {
        $this->markTestIncomplete();
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
    public function testAsIterable(): void
    {
        $this->markTestIncomplete();
    }
}