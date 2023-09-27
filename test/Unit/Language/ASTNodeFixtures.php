<?php

/**
 * PackageFactory.ComponentEngine - Universal View Components for PHP
 *   Copyright (C) 2023 Contributors of PackageFactory.ComponentEngine
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

namespace PackageFactory\ComponentEngine\Test\Unit\Language;

use PackageFactory\ComponentEngine\Language\AST\Node\Access\AccessNode;
use PackageFactory\ComponentEngine\Language\AST\Node\BinaryOperation\BinaryOperationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\BooleanLiteral\BooleanLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\ComponentDeclaration\ComponentDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Expression\ExpressionNode;
use PackageFactory\ComponentEngine\Language\AST\Node\IntegerLiteral\IntegerLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Match\MatchNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Module\ModuleNode;
use PackageFactory\ComponentEngine\Language\AST\Node\NullLiteral\NullLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\PropertyDeclaration\PropertyDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StringLiteral\StringLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StructDeclaration\StructDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\AttributeNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Tag\TagNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TernaryOperation\TernaryOperationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\Text\TextNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TypeReference\TypeReferenceNode;
use PackageFactory\ComponentEngine\Language\AST\Node\UnaryOperation\UnaryOperationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\ValueReference\ValueReferenceNode;
use PackageFactory\ComponentEngine\Language\Lexer\Lexer;
use PackageFactory\ComponentEngine\Language\Parser\BooleanLiteral\BooleanLiteralParser;
use PackageFactory\ComponentEngine\Language\Parser\ComponentDeclaration\ComponentDeclarationParser;
use PackageFactory\ComponentEngine\Language\Parser\EnumDeclaration\EnumDeclarationParser;
use PackageFactory\ComponentEngine\Language\Parser\Expression\ExpressionParser;
use PackageFactory\ComponentEngine\Language\Parser\IntegerLiteral\IntegerLiteralParser;
use PackageFactory\ComponentEngine\Language\Parser\Match\MatchParser;
use PackageFactory\ComponentEngine\Language\Parser\Module\ModuleParser;
use PackageFactory\ComponentEngine\Language\Parser\NullLiteral\NullLiteralParser;
use PackageFactory\ComponentEngine\Language\Parser\PropertyDeclaration\PropertyDeclarationParser;
use PackageFactory\ComponentEngine\Language\Parser\StringLiteral\StringLiteralParser;
use PackageFactory\ComponentEngine\Language\Parser\StructDeclaration\StructDeclarationParser;
use PackageFactory\ComponentEngine\Language\Parser\Tag\TagParser;
use PackageFactory\ComponentEngine\Language\Parser\TemplateLiteral\TemplateLiteralParser;
use PackageFactory\ComponentEngine\Language\Parser\Text\TextParser;
use PackageFactory\ComponentEngine\Language\Parser\TypeReference\TypeReferenceParser;
use PackageFactory\ComponentEngine\Language\Parser\ValueReference\ValueReferenceParser;

final class ASTNodeFixtures
{
    public static function Access(string $sourceAsString): AccessNode
    {
        $expressionNode = self::Expression($sourceAsString);
        $accessNode = $expressionNode->root;
        assert($accessNode instanceof AccessNode);

        return $accessNode;
    }

    public static function Attribute(string $sourceAsString): ?AttributeNode
    {
        $tagNode = self::Tag(sprintf('<a %s/>', $sourceAsString));

        return array_values($tagNode->attributes->items)[0] ?? null;
    }

    public static function BinaryOperation(string $sourceAsString): BinaryOperationNode
    {
        $expressionNode = self::Expression($sourceAsString);
        $binaryOperationNode = $expressionNode->root;
        assert($binaryOperationNode instanceof BinaryOperationNode);

        return $binaryOperationNode;
    }

    public static function BooleanLiteral(string $sourceAsString): BooleanLiteralNode
    {
        $booleanLiteralParser = BooleanLiteralParser::singleton();
        $lexer = new Lexer($sourceAsString);

        return $booleanLiteralParser->parse($lexer);
    }

    public static function ComponentDeclaration(string $sourceAsString): ComponentDeclarationNode
    {
        $componentDeclarationParser = ComponentDeclarationParser::singleton();
        $lexer = new Lexer($sourceAsString);

        return $componentDeclarationParser->parse($lexer);
    }

    public static function EnumDeclaration(string $sourceAsString): EnumDeclarationNode
    {
        $enumDeclarationParser = EnumDeclarationParser::singleton();
        $lexer = new Lexer($sourceAsString);

        return $enumDeclarationParser->parse($lexer);
    }

    public static function Expression(string $sourceAsString): ExpressionNode
    {
        $epxressionParser = new ExpressionParser();
        $lexer = new Lexer($sourceAsString);

        return $epxressionParser->parse($lexer);
    }

    public static function IntegerLiteral(string $sourceAsString): IntegerLiteralNode
    {
        $integerLiteralParser = IntegerLiteralParser::singleton();
        $lexer = new Lexer($sourceAsString);

        return $integerLiteralParser->parse($lexer);
    }

    public static function Match(string $sourceAsString): MatchNode
    {
        $matchParser = MatchParser::singleton();
        $lexer = new Lexer($sourceAsString);

        return $matchParser->parse($lexer);
    }

    public static function Module(string $sourceAsString): ModuleNode
    {
        $moduleParser = ModuleParser::singleton();
        $lexer = new Lexer($sourceAsString);

        return $moduleParser->parse($lexer);
    }

    public static function NullLiteral(string $sourceAsString): NullLiteralNode
    {
        $nullLiteralParser = NullLiteralParser::singleton();
        $lexer = new Lexer($sourceAsString);

        return $nullLiteralParser->parse($lexer);
    }

    public static function PropertyDeclaration(string $sourceAsString): PropertyDeclarationNode
    {
        $propertyDeclarationParser = PropertyDeclarationParser::singleton();
        $lexer = new Lexer($sourceAsString);

        return $propertyDeclarationParser->parse($lexer);
    }

    public static function StringLiteral(string $sourceAsString): StringLiteralNode
    {
        $stringLiteralParser = StringLiteralParser::singleton();
        $lexer = new Lexer($sourceAsString);

        return $stringLiteralParser->parse($lexer);
    }

    public static function StructDeclaration(string $sourceAsString): StructDeclarationNode
    {
        $structDeclarationParser = StructDeclarationParser::singleton();
        $tokens = new Lexer($sourceAsString);

        return $structDeclarationParser->parse($tokens);
    }

    public static function Tag(string $sourceAsString): TagNode
    {
        $tagParser = TagParser::singleton();
        $lexer = new Lexer($sourceAsString);

        return $tagParser->parse($lexer);
    }

    public static function TagContent(string $sourceAsString): null|TextNode|ExpressionNode|TagNode
    {
        $tagNode = self::Tag(sprintf('<div>%s</div>', $sourceAsString));

        return $tagNode->children->items[0] ?? null;
    }

    public static function TemplateLiteral(string $sourceAsString): TemplateLiteralNode
    {
        $templateLiteralParser = TemplateLiteralParser::singleton();
        $lexer = new Lexer($sourceAsString);

        return $templateLiteralParser->parse($lexer);
    }

    public static function TernaryOperation(string $sourceAsString): TernaryOperationNode
    {
        $expressionNode = self::Expression($sourceAsString);
        $ternaryOperationNode = $expressionNode->root;
        assert($ternaryOperationNode instanceof TernaryOperationNode);

        return $ternaryOperationNode;
    }

    public static function Text(string $sourceAsString): ?TextNode
    {
        $textParser = TextParser::singleton();
        $lexer = new Lexer($sourceAsString);

        return $textParser->parse($lexer);
    }

    public static function TypeReference(string $sourceAsString): TypeReferenceNode
    {
        $typeReferenceParser = TypeReferenceParser::singleton();
        $lexer = new Lexer($sourceAsString);

        return $typeReferenceParser->parse($lexer);
    }

    public static function ValueReference(string $sourceAsString): ValueReferenceNode
    {
        $valueReferenceParser = ValueReferenceParser::singleton();
        $lexer = new Lexer($sourceAsString);

        return $valueReferenceParser->parse($lexer);
    }

    public static function UnaryOperation(string $sourceAsString): UnaryOperationNode
    {
        $expressionNode = self::Expression($sourceAsString);
        $ternaryOperationNode = $expressionNode->root;
        assert($ternaryOperationNode instanceof UnaryOperationNode);

        return $ternaryOperationNode;
    }
}
