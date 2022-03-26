<?php

declare(strict_types=1);

namespace PackageFactory\ComponentEngine\Parser\Ast;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;

interface Term
{
    // 
    // This interface acts as a Union Type for the following classes:
    //
    // Expression\ArrayLiteral
    // Expression\ObjectLiteral
    // Expression\TemplateLiteral
    // Expression\BooleanLiteral
    // Expression\NullLiteral
    // Expression\NumberLiteral
    // Expression\StringLiteral
    // Expression\ArrowFunction
    // Expression\Chain
    // Expression\Comparison
    // Expression\Conjunction
    // Expression\DashOperation
    // Expression\Disjunction
    // Expression\Identifier
    // Expression\Negation
    // Expression\PointOperation
    // Expression\Ternary
    // Afx\Tag
    //
}
