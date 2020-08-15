<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Library\Operation;

use PackageFactory\ComponentEngine\Runtime\Context\Key;
use PackageFactory\ComponentEngine\Runtime\Context\Value\IteratorValue;
use PackageFactory\ComponentEngine\Runtime\Context\Value\ListValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Library\Operation;
use PackageFactory\ComponentEngine\Runtime\Runtime;

/**
 * @extends Operation<\Iterator>
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
     * @param ListValue $arguments
     * @return IteratorValue
     */
    public function run(ValueInterface $value, Runtime $runtime, ListValue $arguments): ValueInterface
    {
        $itemCallback = $arguments->get(Key::fromInteger(0), false, $runtime);
        $keyCallback = $arguments->get(Key::fromInteger(1), true, $runtime);

        $iterable = $value->asIterable();
        $iterator = function() use ($runtime, $itemCallback, $keyCallback, $iterable): \Iterator {
            $iteration = ['items' => $iterable];

            if (is_countable($iterable)) {
                $iteration['count'] = count($iterable);
            }

            $index = 0;
            foreach ($iterable as $key => $value) {
                if ($index > 0) {
                    $nextKey = $keyCallback
                        ->call(ListValue::fromArray([$iteration['key']]), true, $runtime)
                        ->getValue() ?? $iteration['key'];
                    $nextValue = $itemCallback
                        ->call(ListValue::fromArray([$iteration['value'], $iteration]), false, $runtime);

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
                $nextKey = $keyCallback
                    ->call(ListValue::fromArray([$iteration['key']]), true, $runtime)
                    ->getValue() ?? $iteration['key'];
                $nextValue = $itemCallback
                    ->call(ListValue::fromArray([$iteration['value'], $iteration]), false, $runtime);

                yield $nextKey => $nextValue;
            }
        };

        return IteratorValue::fromIterator($iterator());
    }
}