<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast;

interface Child
{
    // 
    // This interface acts as a Union Type for the following classes:
    //
    // Afx\Content
    // Afx\Tag
    // Expression\ArrayLiteral
    // Expression\TemplateLiteral
    // Expression\NullLiteral
    // Expression\NumberLiteral
    // Expression\StringLiteral
    // Expression\Chain
    // Expression\Conjunction
    // Expression\DashOperation
    // Expression\Disjunction
    // Expression\Identifier
    // Expression\Ternary
    //
}