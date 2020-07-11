<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Disjunction;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnDisjunction
{
    /**
     * @param Runtime $runtime
     * @param Disjunction $disjunction
     * @return mixed
     */
    public static function evaluate(Runtime $runtime, Disjunction $disjunction)
    {
        $left = OnTerm::evaluate($runtime, $disjunction->getLeft());

        if (self::isTrueish($left)) {
            return $left;
        } else {
            $right = OnTerm::evaluate($runtime, $disjunction->getRight());

            if (self::isTrueish($right)) {
                return $right;
            } elseif ($right === null) {
                return null;
            } elseif ($right === 0.0) {
                return 0.0;
            } else {
                return false;
            }
        }
    }

    /**
     * @param mixed $value
     * @return boolean
     */
    private static function isTrueish($value): bool
    {
        if (is_string($value)) {
            return $value !== '';
        } elseif (is_numeric($value)) {
            return $value !== 0.0;
        } elseif (is_null($value)) {
            return false;
        } elseif (is_bool($value)) {
            return $value;
        } else {
            return (bool) $value;
        }
    }
}

