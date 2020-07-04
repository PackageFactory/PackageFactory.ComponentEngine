<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;

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
        $tagName = $htmlElementConstructor->getTagName();
        return VirtualDOM\Element::create(
            VirtualDOM\ElementType::fromTagName(
                $tagName !== null ? $tagName->getValue() : 'div'
            ),
            VirtualDOM\Attributes::fromArray(
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

