<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;

use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Runtime\Context\Value\AfxValue;
use PackageFactory\ComponentEngine\Runtime\Runtime;
use PackageFactory\VirtualDOM\VirtualDOM;

final class OnHtmlElementConstructor
{
    /**
     * @param Runtime $runtime
     * @param Tag $htmlElementConstructor
     * @return AfxValue
     */
    public static function evaluate(
        Runtime $runtime, 
        Tag $htmlElementConstructor
    ): AfxValue {
        $tagName = $htmlElementConstructor->getTagName();

        return AfxValue::fromComponent(
            VirtualDOM::element(
                $tagName !== null ? $tagName->getValue() : 'div',
                iterator_to_array(
                    OnAttributes::evaluate(
                        $runtime, 
                        $htmlElementConstructor->getAttributes()
                    )
                ),
                OnChildren::evaluate(
                    $runtime,
                    $htmlElementConstructor->getChildren()
                )->getValue()
            )
        );
    }
}

