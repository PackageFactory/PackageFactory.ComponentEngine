<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;

use PackageFactory\ComponentEngine\Parser\Ast\Afx\Content;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Parser\Ast\Child;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;
use PackageFactory\ComponentEngine\Runtime\Runtime;
use PackageFactory\VirtualDOM\Model as VirtualDOM;

final class OnChild
{
    /**
     * @param Runtime $runtime
     * @param Child $child
     * @return \Iterator<int, VirtualDOM\ComponentInterface>
     */
    public static function evaluate(Runtime $runtime, Child $child): \Iterator 
    {
        if ($child instanceof Content) {
            yield VirtualDOM\Text::fromString($child->getValue());
        } elseif ($child instanceof Tag) {
            /** @var VirtualDOM\ComponentInterface $component */
            $component = OnTag::evaluate($runtime, $child)->getValue($runtime);
            yield $component;
        } else {
            /** @var Term $child */
            yield from self::getContentFromValue(
                Expression\OnTerm::evaluate($runtime, $child)->getValue($runtime),
                $runtime
            );
        }
    }

    /**
     * @param mixed $value
     * @return \Iterator<int, VirtualDOM\ComponentInterface>
     */
    private static function getContentFromValue($value, Runtime $runtime): \Iterator
    {
        // @TODO: Find a more consistent solution for this
        if ($value instanceof ValueInterface) {
            $value = $value->getValue($runtime);
        }

        if (is_string($value)) {
            yield VirtualDOM\Text::fromString($value);
        } elseif (is_float($value)) {
            yield VirtualDOM\Text::fromString((string) $value);
        } elseif (is_iterable($value)) {
            foreach ($value as $item) {
                yield from self::getContentFromValue($item, $runtime);
            }
        } elseif (is_null($value)) {
            // Ignore
        } elseif (is_bool($value) && !$value) {
            // Ignore
        } elseif ($value instanceof VirtualDOM\ComponentInterface) {
            yield $value;
        } elseif (is_object($value) && method_exists($value, '__toString')) {
            yield VirtualDOM\Text::fromString((string) $value);
        } else {
            throw new \Exception('@TODO: Illegal content value of type ' . gettype($value));
        }
    }
}

