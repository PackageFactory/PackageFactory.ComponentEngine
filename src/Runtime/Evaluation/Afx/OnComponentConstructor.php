<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;

use PackageFactory\VirtualDOM\Model as VirtualDOM;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\TagName;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnComponentConstructor
{
    /**
     * @param Runtime $runtime
     * @param Tag $componentConstructor
     * @return VirtualDOM\ComponentInterface
     */
    public static function evaluate(Runtime $runtime, Tag $componentConstructor): VirtualDOM\ComponentInterface 
    {
        /** @var TagName $tagName */
        $tagName = $componentConstructor->getTagName();
        $constructor = $runtime->getContext()->getProperty(
            $tagName->getValue()
        );
        $props = iterator_to_array(
            OnProps::evaluate(
                $runtime, 
                $componentConstructor->getAttributes()
            )
        );
        $props['children'] = iterator_to_array(
            OnChildren::evaluate(
                $runtime,
                $componentConstructor->getChildren()
            ),
            false
        );

        return $constructor(
            $runtime->getContext()->withMergedProperties([
                'props' => $props
            ])
        );
    }
}

