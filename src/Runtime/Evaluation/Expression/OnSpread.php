<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Spread;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnSpread
{
    /**
     * @param Runtime $runtime
     * @param Spread $spread
     * @return \Iterator<int|string, ValueInterface<mixed>>
     */
    public static function evaluate(Runtime $runtime, Spread $spread): \Iterator 
    {
        $subject = OnTerm::evaluate($runtime, $spread->getSubject());

        foreach ($subject->asIterable() as $key => $value) {
            yield $key => $value;
        }
    }
}

