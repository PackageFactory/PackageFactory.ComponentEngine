<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Call;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Chain;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Identifier;
use PackageFactory\ComponentEngine\Runtime\Context;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnChain
{
    /**
     * @param Runtime $runtime
     * @param Chain $chain
     * @return mixed
     */
    public static function evaluate(Runtime $runtime, Chain $chain)
    {
        if ($chain->getRoot() instanceof Identifier) {
            /** @var Identifier $identifier */
            $identifier = $chain->getRoot();
            $value = $runtime->getContext()->getProperty($identifier->getValue());
        } else {
            $value = OnTerm::evaluate($runtime, $chain->getRoot());
        }

        foreach ($chain->getSegments() as $segment) {
            $key = $segment->getKey();
            if ($key instanceof Identifier) {
                $key = $key->getValue();
            } else {
                $key = OnTerm::evaluate($runtime, $key);
            }

            if (!is_scalar($key)) {
                throw new \RuntimeException('@TODO: Invalid key');
            }

            if ($value instanceof Context) {
                if (!is_string($key)) {
                    throw new \RuntimeException('@TODO: Invalid key');
                } elseif ($value->hasProperty($key)) {
                    $value = $value->getProperty($key);
                } elseif ($segment->getIsOptional()) {
                    return null;
                } else {
                    throw new \RuntimeException('@TODO: Invalid property access: ' . $key);
                }
            } elseif (is_string($value)) {
                if (!is_numeric($key)) {
                    throw new \RuntimeException('@TODO: Invalid key');
                } elseif ((int) $key < mb_strlen($value)) {
                    $value = $value[(int) $key];
                } else {
                    throw new \RuntimeException('@TODO: Invalid property access: ' . $key);
                }
            } elseif (is_array($value)) {
                if (is_numeric($key) && !is_int($key) && intval($key) == $key) {
                    $key = (int) $key;
                }
                if ((is_int($key) || is_string($key)) && array_key_exists($key, $value)) {
                    $value = $value[$key];
                } elseif ($key === 'map') {
                    $items = $value;
                    $value = function(callable $callback) use ($items) {
                        $result = [];
                        foreach ($items as $item) {
                            $result[] = $callback($item);
                        }
                        return $result;
                    };
                } elseif ($segment->getIsOptional()) {
                    return null;
                } else {
                    throw new \RuntimeException('@TODO: Invalid property access: ' . $key);
                }
            } elseif (is_object($value)) {
                if (!is_string($key)) {
                    throw new \RuntimeException('@TODO: Invalid key');
                } elseif (isset($value->{ $key })) {
                    $value = $value->{ $key };
                } else {
                    $getter = 'get' . ucfirst($key);

                    if (is_callable([$value, $getter])) {
                        try {
                            $value = $value->{ $getter }();
                        } catch (\Throwable $err) {
                            throw new \RuntimeException('@TODO: An error occured during PHP execution: ' . $err->getMessage());
                        }
                    } elseif ($segment->getIsOptional()) {
                        return null;
                    } else {
                        throw new \RuntimeException('@TODO: Invalid property access: ' . $key);
                    }
                }
            } else {
                throw new \RuntimeException('@TODO: Invalid value type: ' . gettype($value));
            }

            if ($call = $segment->getCall()) {
                if ($value instanceof \Closure) {
                    $arguments = [];
                    foreach ($call->getArguments() as $argument) {
                        $arguments[] = OnTerm::evaluate($runtime, $argument);
                    }

                    $value = $value(...$arguments);
                } else {
                    throw new \RuntimeException('@TODO: Invalid call.');
                }
            }
        }

        return $value;
    }
}

