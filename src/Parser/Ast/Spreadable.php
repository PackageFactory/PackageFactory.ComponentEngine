<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast;

interface Spreadable
{
    // 
    // This interface acts as a Union Type for the following classes:
    //
    // Expression\ArrayLiteral
    // Expression\ObjectLiteral
    // Expression\Chain
    // Expression\Conjunction
    // Expression\Disjunction
    // Expression\Identifier
    // Expression\Ternary
    //
}
