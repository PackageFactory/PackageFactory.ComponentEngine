<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;

use PackageFactory\ComponentEngine\Parser\Ast\Child;
use PackageFactory\ComponentEngine\Runtime\Runtime;
use PackageFactory\VirtualDOM\Model\ComponentInterface;

final class OnChildren
{
    /**
     * @param Runtime $runtime
     * @param array<int, Child> $children
     * @return \Iterator<int, ComponentInterface>
     */
    public static function evaluate(Runtime $runtime, array $children): \Iterator 
    {
        foreach ($children as $child) {
            yield from OnChild::evaluate($runtime, $child);
        }
    }
}

