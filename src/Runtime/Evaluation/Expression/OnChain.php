<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Chain;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Identifier;
use PackageFactory\ComponentEngine\Runtime\Context\Key;
use PackageFactory\ComponentEngine\Runtime\Context\Value\ListValue;
use PackageFactory\ComponentEngine\Runtime\Context\Value\NullValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnChain
{
    /**
     * @param Runtime $runtime
     * @param Chain $chain
     * @return ValueInterface<mixed>
     */
    public static function evaluate(Runtime $runtime, Chain $chain): ValueInterface
    {
        if ($chain->getRoot() instanceof Identifier) {
            /** @var Identifier $identifier */
            $identifier = $chain->getRoot();
            $value = $runtime->getContext()->get(Key::fromIdentifier($identifier), true, $runtime);
        } else {
            $value = OnTerm::evaluate($runtime, $chain->getRoot());
        }

        foreach ($chain->getSegments() as $segment) {
            $key = $segment->getKey();
            if ($key instanceof Identifier) {
                $key = Key::fromIdentifier($key);
            } else {
                $key = Key::fromValue(OnTerm::evaluate($runtime, $key));
            }

            /** @var ValueInterface<mixed> $value */
            $value = $value->get($key, $segment->getIsOptional(), $runtime);

            if ($call = $segment->getCall()) {
                $arguments = ListValue::empty();
                foreach ($call->getArguments() as $argument) {
                    $arguments = $arguments->withAddedItem(OnTerm::evaluate($runtime, $argument));
                }
                
                $value = $value->call($arguments, $segment->getIsOptional(), $runtime);
            }
        }

        return $value;
    }
}

