<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime;

interface ContextEvaluatorInterface
{
    public function evaluate(Context $context);
}