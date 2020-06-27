<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;

use PackageFactory\ComponentEngine\Parser\Ast\Afx\Content;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\VirtualDOM;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnChildren
{
    /**
     * @param Runtime $runtime
     * @param array<int, Operand|Content|Tag> $children
     * @return \Iterator<int, VirtualDOM\Node>
     */
    public static function evaluate(Runtime $runtime, array $children): \Iterator 
    {
        foreach ($children as $child) {
            yield from OnChild::evaluate($runtime, $child);
        }
    }
}

