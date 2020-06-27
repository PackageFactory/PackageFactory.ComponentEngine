<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;

use PackageFactory\VirtualDOM;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnFragment
{
    /**
     * @param Runtime $runtime
     * @param Tag $fragment
     * @return VirtualDOM\Fragment
     */
    public static function evaluate(Runtime $runtime, Tag $fragment): VirtualDOM\Fragment 
    {
        return VirtualDOM\Fragment::create(
            ...iterator_to_array(
                OnChildren::evaluate(
                    $runtime,
                    $fragment->getChildren()
                ),
                false
            )
        );
    }
}

