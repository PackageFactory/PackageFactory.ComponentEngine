<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Library\Operation;

use PackageFactory\ComponentEngine\Runtime\Context\Value\IteratorValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Library\Operation;
use PackageFactory\ComponentEngine\Runtime\Runtime;

/**
 * @extends Operation<IteratorValue>
 */
final class Map extends Operation
{
    /**
     * @var null|self
     */
    private static $instance;

    /**
     * @return self
     */
    public static function create(): self
    {
        if (self::$instance) {
            return self::$instance;
        }

        return self::$instance = new self();
    }

    /**
     * @param ValueInterface<mixed> $value
     * @param Runtime $runtime
     * @param array<mixed> $arguments
     * @return ValueInterface<IteratorValue>
     */
    public function run(ValueInterface $value, Runtime $runtime, array $arguments): ValueInterface
    {
        /** @var callable $itemCallback */
        $itemCallback = $arguments[0];

        /** @var null|callable $keyCallback */
        $keyCallback = $arguments[1] ?? null;

        $iterable = $value->asIterable();
        $iterator = function() use ($itemCallback, $keyCallback, $iterable): \Iterator {
            $iteration = ['items' => $iterable];

            if (is_countable($iterable)) {
                $iteration['count'] = count($iterable);
            }

            $index = 0;
            foreach ($iterable as $key => $value) {
                if ($index > 0) {
                    $nextKey = $keyCallback ? $keyCallback($iteration['key'], $iteration) : $iteration['key'];
                    $nextValue = $itemCallback($iteration['value'], $iteration);

                    yield $nextKey => $nextValue;
                }

                $iteration['index'] = $index;
                $iteration['key'] = $key;
                $iteration['value'] = $value;
                $iteration['isFirst'] = $index === 0;
                $iteration['isLast'] = false;
                $iteration['isOdd'] = ($index + 1) % 2 === 0;
                $iteration['isEven'] = ($index + 1) % 2 !== 0;
                $index++;
            }

            if ($index > 0) {
                $iteration['isLast'] = true;
                $nextKey = $keyCallback ? $keyCallback($iteration['key'], $iteration) : $iteration['key'];
                $nextValue = $itemCallback($iteration['value'], $iteration);

                yield $nextKey => $nextValue;
            }
        };

        return IteratorValue::fromIterator($iterator());
    }
}