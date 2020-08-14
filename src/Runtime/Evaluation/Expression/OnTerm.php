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
use PackageFactory\ComponentEngine\Parser\Ast\Expression\StringLiteral;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\TemplateLiteral;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Ternary;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;

final class OnTerm
{
    /**
     * @param Runtime $runtime
     * @param Term $term
     * @return ValueInterface<mixed>
     */
    public static function evaluate(Runtime $runtime, Term $term): ValueInterface
    {
        if ($term instanceof NullLiteral) {
            return OnNullLiteral::evaluate($runtime, $term);
        } elseif ($term instanceof BooleanLiteral) {
            return OnBooleanLiteral::evaluate($runtime, $term);
        } elseif ($term instanceof NumberLiteral) {
            return OnNumberLiteral::evaluate($runtime, $term);
        } elseif ($term instanceof StringLiteral) {
            return OnStringLiteral::evaluate($runtime, $term);
        } elseif ($term instanceof TemplateLiteral) {
            return OnTemplateLiteral::evaluate($runtime, $term);
        } elseif ($term instanceof ArrayLiteral) {
            return OnArrayLiteral::evaluate($runtime, $term);
        } elseif ($term instanceof ObjectLiteral) {
            return OnObjectLiteral::evaluate($runtime, $term);
        } elseif ($term instanceof Negation) {
            return OnNegation::evaluate($runtime, $term);
        } elseif ($term instanceof Conjunction) {
            return OnConjunction::evaluate($runtime, $term);
        } elseif ($term instanceof Identifier) {
            return OnIdentifier::evaluate($runtime, $term);
        } elseif ($term instanceof Disjunction) {
            return OnDisjunction::evaluate($runtime, $term);
        } elseif ($term instanceof PointOperation) {
            return OnPointOperation::evaluate($runtime, $term);
        } elseif ($term instanceof DashOperation) {
            return OnDashOperation::evaluate($runtime, $term);
        } elseif ($term instanceof Comparison) {
            return OnComparison::evaluate($runtime, $term);
        } elseif ($term instanceof Ternary) {
            return OnTernary::evaluate($runtime, $term);
        } elseif ($term instanceof ArrowFunction) {
            return OnArrowFunction::evaluate($runtime, $term);
        } elseif ($term instanceof Chain) {
            return OnChain::evaluate($runtime, $term);
        } elseif ($term instanceof Tag) {
            return Afx\OnTag::evaluate($runtime, $term);
        } else {
            throw new \RuntimeException('@TODO: Illegal Term ' . get_class($term));
        }
    }
}

