<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;

use PackageFactory\ComponentEngine\Parser\Ast\Afx\Content;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Parser\Ast\Child;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
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
            yield OnTag::evaluate($runtime, $child);
        } else {
            /** @var Term $child */
            yield from self::getContentFromValue(
                Expression\OnTerm::evaluate($runtime, $child)->getValue()
            );
        }
    }

    /**
     * @param mixed $value
     * @return \Iterator<int, VirtualDOM\ComponentInterface>
     */
    private static function getContentFromValue($value): \Iterator
    {
        if (is_string($value)) {
            yield VirtualDOM\Text::fromString($value);
        } elseif (is_float($value)) {
            yield VirtualDOM\Text::fromString((string) $value);
        } elseif (is_array($value)) {
            foreach ($value as $item) {
                yield from self::getContentFromValue($item);
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

