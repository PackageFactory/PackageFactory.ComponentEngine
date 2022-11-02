<?php

/**
 * PackageFactory.ComponentEngine - Universal View Components for PHP
 *   Copyright (C) 2022 Contributors of PackageFactory.ComponentEngine
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace PackageFactory\ComponentEngine\Transpiler\Php;

use PackageFactory\ComponentEngine\Parser\Ast\AttributeNode;
use PackageFactory\ComponentEngine\Parser\Ast\ComponentDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\IdentifierNode;
use PackageFactory\ComponentEngine\Parser\Ast\ModuleNode;
use PackageFactory\ComponentEngine\Parser\Ast\PropertyDeclarationNodes;
use PackageFactory\ComponentEngine\Parser\Ast\StringLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\TagNode;

final class Transpiler
{
    public function transpile(ModuleNode $moduleNode): string
    {
        foreach ($moduleNode->exports->items as $exportNode) {
            return match ($exportNode->declaration::class) {
                ComponentDeclarationNode::class => $this->transpileComponentDeclaration($exportNode->declaration),
                default => throw new \Exception('@TODO: Transpile ' . $exportNode::class)
            };
        }

        return '';
    }

    public function transpileComponentDeclaration(ComponentDeclarationNode $componentDeclaration): string
    {
        $lines = [];

        $lines[] = '<?php';
        $lines[] = '';
        $lines[] = 'declare(strict_types=1);';
        $lines[] = '';
        $lines[] = 'namespace Vendor\\Project\\Component;';
        $lines[] = '';
        $lines[] = 'use Vendor\\Project\\BaseClass;';
        $lines[] = '';
        $lines[] = 'final class ' . $componentDeclaration->componentName . ' extends BaseClass';
        $lines[] = '{';
        $lines[] = '    public function __construct(';
        $lines[] = $this->writeConstructorPropertyDeclarations($componentDeclaration->propertyDeclarations);
        $lines[] = '    ) {';
        $lines[] = '    }';
        $lines[] = '';
        $lines[] = '    public function render(): string';
        $lines[] = '    {';
        $lines[] = $this->writeReturnExpression($componentDeclaration->returnExpression);
        $lines[] = '    }';
        $lines[] = '}';
        $lines[] = '';

        return join("\n", $lines);
    }

    public function writeConstructorPropertyDeclarations(PropertyDeclarationNodes $propertyDeclarations): string
    {
        $lines = [];

        foreach ($propertyDeclarations->items as $propertyDeclaration) {
            $lines[] = '        public readonly ' . $propertyDeclaration->type->name . ' $' . $propertyDeclaration->name . ',';
        }

        if ($length = count($lines)) {
            $lines[$length - 1] = substr($lines[$length - 1], 0, -1);
        }

        return join("\n", $lines);
    }

    public function writeReturnExpression(ExpressionNode $returnExpression): string
    {
        return '        return ' . $this->transpileExpression($returnExpression) . ';';
    }

    public function transpileExpression(ExpressionNode $expression): string
    {
        return match($expression->root::class) {
            TagNode::class => $this->transpileTag($expression->root),
            IdentifierNode::class => $this->transpileIdentifier($expression->root),
            default => throw new \Exception('@TODO: Transpile ' . $expression->root::class)
        };
    }

    public function transpileTag(TagNode $tag): string
    {
        $result = sprintf('\'<%s', $tag->tagName);

        foreach ($tag->attributes->items as $attribute) {
            $result .= ' ' . $this->transpileAttribute($attribute);
        }

        $result .= ' />\'';

        return $result;
    }

    public function transpileAttribute(AttributeNode $attribute): string
    {
        return sprintf(
            '%s="%s"', 
            $attribute->name, 
            match ($attribute->value::class) {
                ExpressionNode::class => sprintf(
                    '\' . %s . \'',
                    $this->transpileExpression($attribute->value)
                ),
                StringLiteralNode::class => $this->transpileStringLiteral($attribute->value)
            }
        );
    }

    public function transpileStringLiteral(StringLiteralNode $stringLiteral): string
    {
        throw new \Exception('@TODO: Not implemented');
    }

    public function transpileIdentifier(IdentifierNode $identifier): string
    {
        return '$this->' . $identifier->value;
    }
}
