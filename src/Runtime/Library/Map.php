<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Library;

use PackageFactory\ComponentEngine\Runtime\Context\Value\IterableValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;

final class Map
{
    /**
     * @var ValueInterface
     */
    private $value;

    /**
     * @param ValueInterface $value
     */
    public function __construct(ValueInterface $value)
    {
        $this->value = $value;
    }

    /**
     * @param callable $itemCallback
     * @param null|callable $keyCallback
     * @return void
     */
    public function __invoke(callable $itemCallback, ?callable $keyCallback = null)
    {
        $iterable = $this->value->getValue();
        if (!is_iterable($iterable)) {
            throw new \RuntimeException('@TODO: Cannot map over non-iterable value.');
        }

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