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

use PackageFactory\ComponentEngine\Definition\BinaryOperator;
use PackageFactory\ComponentEngine\Parser\Ast\AttributeNode;
use PackageFactory\ComponentEngine\Parser\Ast\BinaryOperationNode;
use PackageFactory\ComponentEngine\Parser\Ast\ComponentDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\EnumDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\IdentifierNode;
use PackageFactory\ComponentEngine\Parser\Ast\ModuleNode;
use PackageFactory\ComponentEngine\Parser\Ast\NumberLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\PropertyDeclarationNodes;
use PackageFactory\ComponentEngine\Parser\Ast\StringLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\TagContentNode;
use PackageFactory\ComponentEngine\Parser\Ast\TagNode;
use PackageFactory\ComponentEngine\Parser\Ast\TernaryOperationNode;
use PackageFactory\ComponentEngine\Parser\Ast\TextNode;
use PackageFactory\ComponentEngine\Parser\Ast\TypeReferenceNode;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\Expression\ExpressionTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Scope\ComponentScope\ComponentScope;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;

final class Transpiler
{
    public function transpile(ModuleNode $moduleNode): string
    {
        foreach ($moduleNode->exports->items as $exportNode) {
            return match ($exportNode->declaration::class) {
                ComponentDeclarationNode::class => $this->transpileComponentDeclaration($exportNode->declaration),
                EnumDeclarationNode::class => $this->transpileEnumDeclaration($exportNode->declaration),
                default => throw new \Exception('@TODO: Transpile ' . $exportNode->declaration::class)
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

        if (!$componentDeclaration->propertyDeclarations->isEmpty()) {
            $lines[] = '    public function __construct(';
            $lines[] = $this->writeConstructorPropertyDeclarations($componentDeclaration->propertyDeclarations);
            $lines[] = '    ) {';
            $lines[] = '    }';
            $lines[] = '';
        }

        $lines[] = '    public function render(): string';
        $lines[] = '    {';
        $lines[] = $this->writeReturnExpression($componentDeclaration);
        $lines[] = '    }';
        $lines[] = '}';
        $lines[] = '';

        return join("\n", $lines);
    }

    public function transpileEnumDeclaration(EnumDeclarationNode $enumDeclaration): string
    {
        $lines = [];

        $lines[] = '<?php';
        $lines[] = '';
        $lines[] = 'declare(strict_types=1);';
        $lines[] = '';
        $lines[] = 'namespace Vendor\\Project\\Component;';
        $lines[] = '';
        $lines[] = 'enum ' . $enumDeclaration->enumName . ': string';
        $lines[] = '{';

        foreach ($enumDeclaration->memberDeclarations->items as $memberDeclarationNode) {
            $lines[] = '    case ' . $memberDeclarationNode->name . ' = \'' . $memberDeclarationNode->name . '\';';
        }

        $lines[] = '}';
        $lines[] = '';

        return join("\n", $lines);
    }

    public function writeConstructorPropertyDeclarations(PropertyDeclarationNodes $propertyDeclarations): string
    {
        $lines = [];

        foreach ($propertyDeclarations->items as $propertyDeclaration) {
            $lines[] = '        public readonly ' . $this->transpileTypeReference($propertyDeclaration->type) . ' $' . $propertyDeclaration->name . ',';
        }

        if ($length = count($lines)) {
            $lines[$length - 1] = substr($lines[$length - 1], 0, -1);
        }

        return join("\n", $lines);
    }

    public function writeReturnExpression(ComponentDeclarationNode $componentDeclarationNode): string
    {
        $returnExpression = $componentDeclarationNode->returnExpression;
        $transpiledReturnExpression = $this->transpileReturnExpression($returnExpression);
        $expressionTypeResolver = new ExpressionTypeResolver(
            scope: new ComponentScope($componentDeclarationNode)
        );
        $returnTypeIsString = StringType::get()->is(
            $expressionTypeResolver->resolveTypeOf($returnExpression)
        );

        if (!$returnTypeIsString) {
            $transpiledReturnExpression = '(string) ' . $transpiledReturnExpression;
        }

        return '        return ' . $transpiledReturnExpression . ';';
    }

    public function transpileExpression(ExpressionNode $expression): string
    {
        return match ($expression->root::class) {
            TagNode::class => $this->transpileTag($expression->root),
            IdentifierNode::class => $this->transpileIdentifier($expression->root),
            TernaryOperationNode::class => $this->transpileTernaryOperation($expression->root),
            BinaryOperationNode::class => $this->transpileBinaryOperation($expression->root),
            NumberLiteralNode::class => $this->transpileNumberLiteral($expression->root),
            default => throw new \Exception('@TODO: Transpile ' . $expression->root::class)
        };
    }

    public function transpileReturnExpression(ExpressionNode $returnExpression): string
    {
        return match ($returnExpression->root::class) {
            TagNode::class => sprintf(
                '\'%s\'',
                $this->transpileTag($returnExpression->root)
            ),
            IdentifierNode::class => $this->transpileIdentifier($returnExpression->root),
            TernaryOperationNode::class => $this->transpileTernaryReturnExpression($returnExpression->root),
            BinaryOperationNode::class => $this->transpileBinaryOperation($returnExpression->root),
            default => throw new \Exception('@TODO: Transpile ' . $returnExpression->root::class)
        };
    }

    public function transpileTag(TagNode $tag): string
    {
        $result = sprintf('<%s', $tag->tagName);

        foreach ($tag->attributes->items as $attribute) {
            $result .= ' ' . $this->transpileAttribute($attribute);
        }

        if ($tag->isSelfClosing) {
            $result .= ' />';
        } else {
            $result .= '>';

            foreach ($tag->children->items as $child) {
                $result .= $this->transpileTagContent($child);
            }

            $result .= sprintf('</%s>', $tag->tagName);
        }

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
        return $stringLiteral->value;
    }

    public function transpileIdentifier(IdentifierNode $identifier): string
    {
        return '$this->' . $identifier->value;
    }

    public function transpileTagContent(TagContentNode $tagContent): string
    {
        return match ($tagContent->root::class) {
            TextNode::class => $this->transpileText($tagContent->root),
            ExpressionNode::class => sprintf(
                '\' . %s . \'',
                $this->transpileExpression($tagContent->root)
            ),
            TagNode::class => $this->transpileTag($tagContent->root)
        };
    }

    public function transpileText(TextNode $textNode): string
    {
        return $textNode->value;
    }

    public function transpileTernaryOperation(TernaryOperationNode $ternaryOperation): string
    {
        return sprintf(
            '(%s ? %s : %s)',
            $this->transpileExpression($ternaryOperation->condition),
            $this->transpileExpression($ternaryOperation->true),
            $this->transpileExpression($ternaryOperation->false)
        );
    }

    public function transpileTernaryReturnExpression(TernaryOperationNode $ternaryOperation): string
    {
        return sprintf(
            '(%s ? %s : %s)',
            $this->transpileReturnExpression($ternaryOperation->condition),
            $this->transpileReturnExpression($ternaryOperation->true),
            $this->transpileReturnExpression($ternaryOperation->false)
        );
    }

    public function transpileBinaryOperation(BinaryOperationNode $binaryOperation): string
    {
        $result = $this->transpileExpression($binaryOperation->operands->first);
        $operator = sprintf(' %s ', $this->transpileBinaryOperator($binaryOperation->operator));

        foreach ($binaryOperation->operands->rest as $operandNode) {
            $result .= $operator;
            $result .= $this->transpileExpression($operandNode);
        }

        return sprintf('(%s)', $result);
    }

    public function transpileBinaryOperator(BinaryOperator $binaryOperator): string
    {
        return match ($binaryOperator) {
            BinaryOperator::LESS_THAN_OR_EQUAL => '<=',
            BinaryOperator::MULTIPLY_BY => '*',
            BinaryOperator::PLUS => '+',
            BinaryOperator::MODULO => '%',
            BinaryOperator::DIVIDE_BY => '/',
            default => throw new \Exception('@TODO: Transpile binary operator ' . $binaryOperator->name)
        };
    }

    public function transpileTypeReference(TypeReferenceNode $typeReference): string
    {
        if ($typeReference->name === 'number') {
            return 'int|float';
        }

        return $typeReference->name;
    }

    public function transpileNumberLiteral(NumberLiteralNode $numberLiteral): string
    {
        return $numberLiteral->value;
    }
}
