<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Afx;

use PackageFactory\VirtualDOM;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Content;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Runtime\Expression\OnExpression;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnChild
{
    /**
     * @param Runtime $runtime
     * @param Content|Tag|Operand $child
     * @return \Iterator<int, VirtualDOM\Node>
     */
    public static function evaluate(Runtime $runtime, $child): \Iterator 
    {
        if ($child instanceof Content) {
            yield VirtualDOM\Text::createFromString($child->getValue());
        } elseif ($child instanceof Tag) {
            yield OnTag::evaluate($runtime, $child);
        } else {
            yield from self::getContentFromValue(
                OnExpression::evaluate($runtime, $child)
            );
        }
    }

    /**
     * @param mixed $value
     * @return \Iterator<int, VirtualDOM\Node>
     */
    private static function getContentFromValue($value): \Iterator
    {
        if (is_string($value)) {
            yield VirtualDOM\Text::createFromString($value);
        } elseif (is_float($value)) {
            yield VirtualDOM\Text::createFromString((string) $value);
        } elseif (is_array($value)) {
            foreach ($value as $item) {
                yield from self::getContentFromValue($item);
            }
        } elseif (is_null($value)) {
            // Ignore
        } elseif (is_bool($value) && !$value) {
            // Ignore
        } elseif ($value instanceof VirtualDOM\Node) {
            yield $value;
        } elseif (is_object($value) && method_exists($value, '__toString')) {
            yield VirtualDOM\Text::createFromString((string) $value);
        } else {
            throw new \Exception('@TODO: Illegal content value of type ' . gettype($value));
        }
    }
}

