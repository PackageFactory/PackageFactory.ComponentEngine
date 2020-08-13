<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Library;

use PackageFactory\ComponentEngine\Runtime\Context\Value\IterableValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class Map extends Operation
{
    /**
     * @param ValueInterface $value
     * @param Runtime $runtime
     * @param array<mixed> $arguments
     * @return void
     */
    public function run(ValueInterface $value, Runtime $runtime, array $arguments): ValueInterface
    {
        /** @var callable $itemCallback */
        $itemCallback = $arguments[0];

        /** @var null|callable $keyCallback */
        $keyCallback = $arguments[1] ?? null;

        $iterable = $value->asIterable($runtime);
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

        return IterableValue::fromIterator($iterator());
    }
}