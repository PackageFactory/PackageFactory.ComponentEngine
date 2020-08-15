<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;

use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\TagName;
use PackageFactory\ComponentEngine\Runtime\Context\Key;
use PackageFactory\ComponentEngine\Runtime\Context\Value\DictionaryValue;
use PackageFactory\ComponentEngine\Runtime\Context\Value\ListValue;
use PackageFactory\ComponentEngine\Runtime\Context\Value\NullValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnComponentConstructor
{
    /**
     * @param Runtime $runtime
     * @param Tag $componentConstructor
     * @return ValueInterface<mixed>
     */
    public static function evaluate(Runtime $runtime, Tag $componentConstructor): ValueInterface 
    {
        /** @var TagName $tagName */
        $tagName = $componentConstructor->getTagName();
        $constructor = $runtime->getContext()->get(Key::fromTagName($tagName), false, $runtime);

        $props = DictionaryValue::fromValueIterator(OnProps::evaluate(
            $runtime, 
            $componentConstructor->getAttributes()
        ))->withAddedProperty('children', OnChildren::evaluate(
            $runtime,
            $componentConstructor->getChildren()
        ));

        return $constructor->call(ListValue::empty()->withAddedItem($props), false, $runtime);
    }
}

