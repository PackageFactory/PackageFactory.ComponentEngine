<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\ArrayLiteral;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Spread;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnArrayLiteral
{
    /**
     * @param Runtime $runtime
     * @param ArrayLiteral $arrayLiteral
     * @return array<int, mixed>
     */
    public static function evaluate(Runtime $runtime, ArrayLiteral $arrayLiteral): array 
    {
        $result = [];

        foreach ($arrayLiteral->getItems() as $item) {
            if ($item instanceof Spread) {
                $index = 0;
                foreach (OnSpread::evaluate($runtime, $item) as $key => $value) {
                    if ($key === $index) {
                        $result[] = $value;
                        $index++;
                    } else {
                        throw new \RuntimeException('@TODO: Cannot spread non-numerical array');
                    }
                }
            } else {
                /** @var Term $item */
                $item = $item;
                $result[] = OnTerm::evaluate($runtime, $item);
            }
        }

        return $result;
    }
}

