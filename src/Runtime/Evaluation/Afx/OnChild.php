<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;

use PackageFactory\ComponentEngine\Parser\Ast\Afx\Content;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Parser\Ast\Child;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Runtime\Context\Value\AfxValue;
use PackageFactory\ComponentEngine\Runtime\Context\Value\BooleanValue;
use PackageFactory\ComponentEngine\Runtime\Context\Value\NullValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;
use PackageFactory\ComponentEngine\Runtime\Runtime;
use PackageFactory\VirtualDOM\Model as VirtualDOM;

final class OnChild
{
    /**
     * @param Runtime $runtime
     * @param Child $child
     * @return \Iterator<int, ValueInterface<mixed>>
     */
    public static function evaluate(Runtime $runtime, Child $child): \Iterator
    {
        if ($child instanceof Content) {
            yield AfxValue::fromComponent(VirtualDOM\Text::fromString($child->getValue()));
        } elseif ($child instanceof Tag) {
            yield OnTag::evaluate($runtime, $child);
        } else {
            /** @var Term $child */
            yield from self::getContentFromValue(Expression\OnTerm::evaluate($runtime, $child));
        }
    }

    /**
     * @param ValueInterface<mixed> $value
     * @return \Iterator<int, ValueInterface<mixed>>
     */
    private static function getContentFromValue(ValueInterface $value): \Iterator
    {
        if ($value->isCastableToString()) {
            yield AfxValue::fromComponent(VirtualDOM\Text::fromString($value->asStringValue()->getValue()));
        } elseif ($value instanceof AfxValue) {
            yield $value;
        } elseif ($value instanceof BooleanValue && $value->getValue() === false) {
            // Ignore
        } elseif ($value instanceof NullValue) {
            // Ignore
        } else {
            foreach ($value->asIterable() as $item) {
                yield from self::getContentFromValue($item);
            }
        }
    }
}

