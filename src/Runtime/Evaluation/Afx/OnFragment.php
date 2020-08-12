<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;

use PackageFactory\VirtualDOM\VirtualDOM;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Runtime\Context\Value\AfxValue;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnFragment
{
    /**
     * @param Runtime $runtime
     * @param Tag $fragment
     * @return AfxValue
     */
    public static function evaluate(Runtime $runtime, Tag $fragment): AfxValue
    {
        return AfxValue::fromComponent(
            VirtualDOM::fragment(
                iterator_to_array(
                    OnChildren::evaluate(
                        $runtime,
                        $fragment->getChildren()
                    ),
                    false
                )
            )
        );
    }
}

