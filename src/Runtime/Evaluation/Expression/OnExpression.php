<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Runtime\Runtime;
use PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\ArrayLiteral;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\ArrowFunction;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\BooleanLiteral;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Chain;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Comparison;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Conjunction;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\DashOperation;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Disjunction;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Identifier;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Negation;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\NullLiteral;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\NumberLiteral;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\ObjectLiteral;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\PointOperation;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Spread;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\StringLiteral;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\TemplateLiteral;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Ternary;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;

final class OnExpression
{
    /**
     * @param Runtime $runtime
     * @param Operand $operand
     * @return void
     */
    public static function evaluate(Runtime $runtime, $operand) 
    {
        if ($operand instanceof NullLiteral) {
            return OnNullLiteral::evaluate($runtime, $operand);
        } elseif ($operand instanceof BooleanLiteral) {
            return OnBooleanLiteral::evaluate($runtime, $operand);
        } elseif ($operand instanceof NumberLiteral) {
            return OnNumberLiteral::evaluate($runtime, $operand);
        } elseif ($operand instanceof StringLiteral) {
            return OnStringLiteral::evaluate($runtime, $operand);
        } elseif ($operand instanceof TemplateLiteral) {
            return OnTemplateLiteral::evaluate($runtime, $operand);
        } elseif ($operand instanceof ArrayLiteral) {
            return OnArrayLiteral::evaluate($runtime, $operand);
        } elseif ($operand instanceof ObjectLiteral) {
            return OnObjectLiteral::evaluate($runtime, $operand);
        } elseif ($operand instanceof Negation) {
            return OnNegation::evaluate($runtime, $operand);
        } elseif ($operand instanceof Spread) {
            return OnSpread::evaluate($runtime, $operand);
        } elseif ($operand instanceof Conjunction) {
            return OnConjunction::evaluate($runtime, $operand);
        } elseif ($operand instanceof Identifier) {
            return OnIdentifier::evaluate($runtime, $operand);
        } elseif ($operand instanceof Disjunction) {
            return OnDisjunction::evaluate($runtime, $operand);
        } elseif ($operand instanceof PointOperation) {
            return OnPointOperation::evaluate($runtime, $operand);
        } elseif ($operand instanceof DashOperation) {
            return OnDashOperation::evaluate($runtime, $operand);
        } elseif ($operand instanceof Comparison) {
            return OnComparison::evaluate($runtime, $operand);
        } elseif ($operand instanceof Ternary) {
            return OnTernary::evaluate($runtime, $operand);
        } elseif ($operand instanceof ArrowFunction) {
            return OnArrowFunction::evaluate($runtime, $operand);
        } elseif ($operand instanceof Chain) {
            return OnChain::evaluate($runtime, $operand);
        } elseif ($operand instanceof Tag) {
            return Afx\OnTag::evaluate($runtime, $operand);
        } else {
            throw new \RuntimeException('@TODO: Illegal Operand ' . get_class($operand));
        }
    }
}

