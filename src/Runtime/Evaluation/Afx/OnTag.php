<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;

use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Runtime\Context\Value\AfxValue;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnTag
{
    /**
     * @param Runtime $runtime
     * @param Tag $tag
     * @return AfxValue
     */
    public static function evaluate(Runtime $runtime, Tag $tag): AfxValue 
    {
        $tagName = $tag->getTagName();

        if ($tag->getIsFragment()) {
            return OnFragment::evaluate($runtime, $tag);
        } elseif ($tagName !== null && ctype_lower($tagName->getValue()[0])) {
            return AfxValue::fromComponent(
                OnHtmlElementConstructor::evaluate($runtime, $tag)
            );
        } else {
            return AfxValue::fromComponent(
                OnComponentConstructor::evaluate($runtime, $tag)
            );
        }
    }
}

