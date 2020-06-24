<?php
namespace PackageFactory\ComponentEngine\Evaluation;

/**
 * @template T
 */
interface ExpressionEvaluatorInterface
{
    /**
     * @param Operand $name
     * @return T
     */
    public function evaluate($root);
}