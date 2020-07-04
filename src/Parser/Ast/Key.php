<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast;

interface Key
{
    // 
    // This interface acts as a Union Type for the following classes:
    //
    // Expression\Identifier
    // Expression\StringLiteral
    // Expression\NumberLiteral
    // Expression\TemplateLiteral
    // Expression\Chain
    // Expression\DashOperation
    //
}