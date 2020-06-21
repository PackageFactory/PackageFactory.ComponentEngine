<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime;

use PackageFactory\ComponentEngine\Pragma\AfxPragmaInterface;

interface AfxEvaluatorInterface
{
    public function evaluate(AfxPragmaInterface $pragma, Context $context);
}