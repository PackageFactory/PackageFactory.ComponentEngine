<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;

use PackageFactory\ComponentEngine\Parser\Ast\Afx\Content;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnChildren
{
    /**
     * @param Runtime $runtime
     * @param array $children
     * @return \Iterator<int, Content|Tag|Operand>
     */
    public static function evaluate(Runtime $runtime, array $children): \Iterator 
    {
        foreach ($children as $child) {
            yield from OnChild::evaluate($runtime, $child);
        }
    }
}

