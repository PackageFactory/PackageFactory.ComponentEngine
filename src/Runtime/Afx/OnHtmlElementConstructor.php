<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Afx;

use PackageFactory\VirtualDOM;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnHtmlElementConstructor
{
    /**
     * @param Runtime $runtime
     * @param Tag $htmlElementConstructor
     * @return VirtualDOM\Element
     */
    public static function evaluate(
        Runtime $runtime, 
        Tag $htmlElementConstructor
    ): VirtualDOM\Element {
        return VirtualDOM\Element::create(
            VirtualDOM\ElementType::createFromTagName(
                $htmlElementConstructor->getTagName()->getValue()
            ),
            VirtualDOM\Attributes::createFromArray(
                iterator_to_array(
                    OnAttributes::evaluate(
                        $runtime, 
                        $htmlElementConstructor->getAttributes()
                    ),
                    false
                )
            ),
            VirtualDOM\NodeList::create(
                ...iterator_to_array(
                    OnChildren::evaluate(
                        $runtime,
                        $htmlElementConstructor->getChildren()
                    ),
                    false
                )
            )
        );
    }
}

