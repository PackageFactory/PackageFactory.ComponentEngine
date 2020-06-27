<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Spread;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnSpread
{
    /**
     * @param Runtime $runtime
     * @param Spread $Spread
     * @return \Iterator<string, void>
     */
    public static function evaluate(Runtime $runtime, Spread $spread): \Iterator 
    {
        $subject = OnExpression::evaluate($runtime, $spread->getSubject());

        if (is_object($subject)) {
            $subject = (array) $subject;
        }

        if (is_array($subject)) {
            foreach ($subject as $key => $value) {
                yield $key => $value;
            }
        } else {
            throw new \Exception('@TODO: cannot spread value of type ' . gettype($subject));
        }
    }
}

