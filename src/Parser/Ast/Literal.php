<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast;

interface Literal
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
    //
}