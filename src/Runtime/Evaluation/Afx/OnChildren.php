<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;

use PackageFactory\ComponentEngine\Parser\Ast\Child;
use PackageFactory\ComponentEngine\Runtime\Context\Value\ListValue;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnChildren
{
    /**
     * @param Runtime $runtime
     * @param array<int, Child> $children
     * @return ListValue
     */
    public static function evaluate(Runtime $runtime, array $children): ListValue 
    {
        $iterator = function() use ($runtime, $children) {
            foreach ($children as $child) {
                yield from OnChild::evaluate($runtime, $child);
            }
        };

        return ListValue::fromValueIterator($iterator());
    }
}

