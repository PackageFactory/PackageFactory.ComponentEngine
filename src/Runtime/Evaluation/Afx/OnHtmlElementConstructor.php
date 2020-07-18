<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;

use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Runtime\Runtime;
use PackageFactory\VirtualDOM\VirtualDOM;
use PackageFactory\VirtualDOM\Model\ComponentInterface;

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
    ): ComponentInterface {
        $tagName = $htmlElementConstructor->getTagName();

        return VirtualDOM::element(
            $tagName !== null ? $tagName->getValue() : 'div',
            iterator_to_array(
                OnAttributes::evaluate(
                    $runtime, 
                    $htmlElementConstructor->getAttributes()
                )
            ),
            iterator_to_array(
                OnChildren::evaluate(
                    $runtime,
                    $htmlElementConstructor->getChildren()
                ),
                false
            )
        );
    }
}

