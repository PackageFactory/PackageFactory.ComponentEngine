<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Afx;

use PackageFactory\VirtualDOM;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnTag
{
    /**
     * @param Runtime $runtime
     * @param Tag $tag
     * @return VirtualDOM\Node
     */
    public static function evaluate(Runtime $runtime, Tag $tag): VirtualDOM\Node 
    {
        if ($tag->getIsFragment()) {
            return OnFragment::evaluate($runtime, $tag);
        } elseif (ctype_lower($tag->getTagName()->getValue()[0])) {
            return OnHtmlElementConstructor::evaluate($runtime, $tag);
        } else {
            return OnComponentConstructor::evaluate($runtime, $tag);
        }
    }
}

