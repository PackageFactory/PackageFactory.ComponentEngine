<?php
namespace PackageFactory\ComponentEngine\Evaluation;

use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;

/**
 * @template T
 */
interface AfxEvaluatorInterface
{
    /**
     * @param Tag $root
     * @return T
     */
    public function evaluate(Tag $root);
}