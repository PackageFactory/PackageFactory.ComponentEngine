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

namespace PackageFactory\ComponentEngine\Target\Php\Transpiler\EnumDeclaration;

use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\EnumDeclaration\EnumMemberDeclarationNode;
use PackageFactory\ComponentEngine\Language\AST\Node\IntegerLiteral\IntegerLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\StringLiteral\StringLiteralNode;

final class EnumDeclarationTranspiler
{
    public function __construct(
        private readonly EnumDeclarationStrategyInterface $strategy
    ) {
    }

    public function transpile(EnumDeclarationNode $enumDeclarationNode): string
    {
        $className = $this->strategy->getClassNameFor($enumDeclarationNode);

        $lines = [];

        $lines[] = '<?php';
        $lines[] = '';
        $lines[] = 'declare(strict_types=1);';
        $lines[] = '';
        $lines[] = 'namespace ' . $className->getNamespace() . ';';
        $lines[] = '';
        $lines[] = 'enum ' . $className->getShortClassName() . ' : ' . $this->transpileBackingType($enumDeclarationNode);
        $lines[] = '{';

        foreach ($enumDeclarationNode->members->items as $memberDeclarationNode) {
            $lines[] = '    case ' . $memberDeclarationNode->name->value->value . ' = ' . $this->transpileMemberValue($memberDeclarationNode) . ';';
        }

        $lines[] = '}';
        $lines[] = '';

        return join("\n", $lines);
    }

    private function transpileBackingType(EnumDeclarationNode $enumDeclarationNode): string
    {
        foreach ($enumDeclarationNode->members->items as $memberDeclarationNode) {
            if ($memberDeclarationNode->value?->value instanceof IntegerLiteralNode) {
                return 'int';
            } else {
                return 'string';
            }
        }

        return 'string';
    }

    private function transpileMemberValue(EnumMemberDeclarationNode $enumMemberDeclarationNode): string
    {
        if ($enumMemberDeclarationNode->value?->value instanceof IntegerLiteralNode) {
            return $enumMemberDeclarationNode->value->value->value;
        } else if ($enumMemberDeclarationNode->value?->value instanceof StringLiteralNode) {
            return '\'' . $enumMemberDeclarationNode->value->value->value . '\'';
        } else {
            return '\'' . $enumMemberDeclarationNode->name->value->value . '\'';
        }
    }
}
