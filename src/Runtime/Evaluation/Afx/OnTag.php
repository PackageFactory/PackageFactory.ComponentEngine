<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;

use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnTag
{
    /**
     * @param Runtime $runtime
     * @param Tag $tag
     * @return ValueInterface<mixed>
     */
    public static function evaluate(Runtime $runtime, Tag $tag): ValueInterface 
    {
        $tagName = $tag->getTagName();

        if ($tag->getIsFragment()) {
            return OnFragment::evaluate($runtime, $tag);
        } elseif ($tagName !== null && ctype_lower($tagName->getValue()[0])) {
            return OnHtmlElementConstructor::evaluate($runtime, $tag);
        } else {
            return OnComponentConstructor::evaluate($runtime, $tag);
        }
    }
}

