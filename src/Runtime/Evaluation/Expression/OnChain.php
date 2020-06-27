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
        $segments = $chain->getSegments();
        $root = array_shift($segments);

        if ($root->getSubject() instanceof Identifier) {
            array_unshift($segments, $root);
            $value = $runtime->getContext();
        } else {
            $value = OnExpression::evaluate($runtime, $root->getSubject());
        }

        foreach ($segments as $segment) {
            $subject = $segment->getSubject();
            if ($subject instanceof Identifier) {
                $key = $subject->getValue();
            } elseif ($subject instanceof Call) {
                if ($value instanceof \Closure) {
                    $arguments = [];
                    foreach ($subject->getArguments() as $argument) {
                        $arguments[] = OnExpression::evaluate($runtime, $argument);
                    }

                    return $value(...$arguments);
                } else {
                    throw new \Exception('@TODO: Subject cannot be called.');
                }
            } else {
                $key = OnExpression::evaluate($runtime, $subject);
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
                } elseif ($key < mb_strlen($value)) {
                    $value = $value[$key];
                } elseif ($segment->getIsOptional()) {
                    return null;
                } else {
                    throw new \RuntimeException('@TODO: Invalid property access: ' . $key);
                }
            } elseif (is_array($value)) {
                if (isset($value[$key])) {
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
                    return $value->{ $key };
                } else {
                    $getter = 'get' . ucfirst($key);

                    if (is_callable([$value, $getter])) {
                        try {
                            return $value->{ $getter }();
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
        }

        return $value;
    }
}

