<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluate;

use PackageFactory\VirtualDOM;
use PackageFactory\ComponentEngine\Parser\Ast;
use PackageFactory\ComponentEngine\Runtime\Context;

/**
 * @implements \IteratorAggregate<int, Context>
 */
final class Iteration implements \IteratorAggregate
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var iterable<mixed>
     */
    private $items;

    /**
     * @var string
     */
    private $key = 'itemKey';

    /**
     * @var string
     */
    private $as = 'item';

    /**
     * @var string
     */
    private $it = 'it';

    /**
     * @param Context $context
     * @param iterable<mixed> $items
     * @param string $key
     * @param string $as
     * @param string $it
     */
    private function __construct(
        Context $context,
        iterable $items,
        string $key = 'itemKey',
        string $as = 'item',
        string $it = 'it'
    ) {
        $this->context = $context;
        $this->items = $items;
        $this->key = $key;
        $this->as = $as;
        $this->it = $it;
    }

    public static function evaluate(
        Context $context, 
        Ast\Attribute $attribute
    ): self {
        if ($attribute->getValue() instanceof Ast\Expression) {
            $value = Expression::evaluate($context, $attribute->getValue());

            if (!is_array($value)) {
                throw new \RuntimeException('@TODO: Invalid Attribute value for c:map');
            }
            elseif (!isset($value['items'])) {
                throw new \RuntimeException('@TODO: Attribute value for c:map must define items');
            }
            else {
                $items = $value['items'];

                if (isset($value['key'])) {
                    $key = $value['key'];
                }
                else {
                    $key = 'itemKey';
                }

                if (isset($value['as'])) {
                    $as = $value['as'];
                }
                else {
                    $as = 'item';
                }

                if (isset($value['it'])) {
                    $it = $value['it'];
                }
                else {
                    $it = 'it';
                }

                return new self($context, $items, $key, $as, $it);
            }
        }
        else {
            throw new \RuntimeException('@TODO: Invalid Attribute value for c:map');
        }
    }

    /**
     * @return iterable<int, Context>
     */
    public function getIterator()
    {
        $index = 0;
        $isFirst = true;
        $count = is_countable($this->items) ? count($this->items) : null;

        foreach ($this->items as $key => $item) {
            $context = [];
            $context[$this->it]['index'] = $index;
            $context[$this->it]['isFirst'] = $isFirst;
            $context[$this->it]['isEven'] = (($index + 1) % 2) === 0;
            $context[$this->it]['isOdd'] = (($index + 1) % 2) === 1;

            if ($count !== null) {
                $context[$this->it]['count'] = $count;
                $context[$this->it]['isLast'] = $index === $count - 1;
            }

            $context[$this->key] = $key;
            $context[$this->as] = $item;

            yield $index => $this->context->withMergedProperties($context);

            $isFirst = false;
            $index++;
        }
    }
}
