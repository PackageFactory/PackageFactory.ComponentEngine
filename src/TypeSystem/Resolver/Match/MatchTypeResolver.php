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

namespace PackageFactory\ComponentEngine\TypeSystem\Resolver\Match;

use PackageFactory\ComponentEngine\Definition\AccessType;
use PackageFactory\ComponentEngine\Parser\Ast\AccessNode;
use PackageFactory\ComponentEngine\Parser\Ast\BooleanLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNodes;
use PackageFactory\ComponentEngine\Parser\Ast\IdentifierNode;
use PackageFactory\ComponentEngine\Parser\Ast\MatchNode;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\Expression\ExpressionTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\Identifier\IdentifierTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\Type\BooleanType\BooleanType;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumStaticType;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumType;
use PackageFactory\ComponentEngine\TypeSystem\Type\UnionType\UnionType;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

final class MatchTypeResolver
{
    public function __construct(
        private readonly ScopeInterface $scope
    ) {
    }

    private function resolveTypeOfBooleanMatch(MatchNode $matchNode): TypeInterface
    {
        $expressionTypeResolver = new ExpressionTypeResolver(
            scope: $this->scope
        );

        if ($matchNode->subject->root instanceof BooleanLiteralNode) {
            foreach ($matchNode->arms->items as $matchArmNode) {
                if ($matchArmNode->left === null) {
                    throw new \Exception('@TODO: Not implemented: Default Arm');
                } else {
                    foreach ($matchArmNode->left->items as $leftNode) {
                        if ($leftNode->root instanceof BooleanLiteralNode) {
                            if ($leftNode->root->value === $matchNode->subject->root->value) {
                                return $expressionTypeResolver->resolveTypeOf(
                                    $matchArmNode->right
                                );
                            }
                        } else {
                            throw new \Exception('@TODO: Not implemented: Incompatible Arm');
                        }
                    }
                }
            }

            throw new \Exception('@TODO: Not implemented: Incomplete Match');
        } else {
            $types = [];

            $defaultArmPresent = false;
            foreach ($matchNode->arms->items as $matchArmNode) {
                if ($defaultArmPresent) {
                    throw new \Exception('@TODO: Multiple illegal default arms');
                }
                if ($matchArmNode->left === null) {
                    $defaultArmPresent = true;
                }
                $types[] = $expressionTypeResolver->resolveTypeOf(
                    $matchArmNode->right
                );
            }

            // @TODO: Ensure that match is complete

            return UnionType::of(...$types);
        }
    }

    private function resolveTypeOfEnumMatch(MatchNode $matchNode, EnumType $subjectEnumType): TypeInterface
    {
        $expressionTypeResolver = new ExpressionTypeResolver(
            scope: $this->scope
        );
        $types = [];

        $defaultArmPresent = false;
        $matchedEnumMembers = [];
        
        foreach ($matchNode->arms->items as $matchArmNode) {
            if ($defaultArmPresent) {
                throw new \Exception('@TODO Error: Multiple illegal default arms');
            }
            if ($matchArmNode->left === null) {
                $defaultArmPresent = true;
            } else {
                foreach ($this->extractEnumTypeIdentifierAndEnumMemberIdentifier($matchArmNode->left) as [$enumIdentifier, $enumPath]) {
                    $enumType = (new IdentifierTypeResolver(scope: $this->scope))->resolveTypeOf($enumIdentifier);
                    
                    if (!$enumType instanceof EnumStaticType) {
                        throw new \Exception('@TODO Error: To be matched enum must be referenced static');
                    }

                    if (!$enumType->is($subjectEnumType)) {
                        throw new \Error('@TODO Error: incompatible enum match: got ' . $enumType->enumName . ' expected ' . $subjectEnumType->enumName);
                    }

                    if (isset($matchedEnumMembers[$enumPath->value])) {
                        throw new \Error('@TODO Error: Enum path ' . $enumPath->value . ' was already defined once in this match and cannot be used twice');
                    }

                    $matchedEnumMembers[$enumPath->value] = true;
                }
            }

            $types[] = $expressionTypeResolver->resolveTypeOf(
                $matchArmNode->right
            );
        }

        if (!$defaultArmPresent) {
            foreach ($subjectEnumType->members as $member) {
                if (!isset($matchedEnumMembers[$member])) {
                    throw new \Error('@TODO Error: member ' . $member . ' not checked');
                }
            }
        }

        return UnionType::of(...$types);
    }

    /**
     * @return \Iterator<array{0:IdentifierNode, 1:IdentifierNode}>
     */
    private function extractEnumTypeIdentifierAndEnumMemberIdentifier(ExpressionNodes $left)
    {
        foreach ($left->items as $expressionNode) {
            $accessNode = $expressionNode->root;
            if (
                !($accessNode instanceof AccessNode
                    && $accessNode->root instanceof ExpressionNode
                    && $accessNode->root->root instanceof IdentifierNode
                    && count($accessNode->chain->items) === 1
                    && $accessNode->chain->items[0]->accessType === AccessType::MANDATORY
                )
            ) {
                throw new \Error('@TODO Error: To be matched enum value should be referenced like: `Enum.B`');
            }

            yield [
                $accessNode->root->root,
                $accessNode->chain->items[0]->accessor
            ];
        }
    }

    public function resolveTypeOf(MatchNode $matchNode): TypeInterface
    {
        $expressionTypeResolver = new ExpressionTypeResolver(
            scope: $this->scope
        );
        $typeOfSubject = $expressionTypeResolver->resolveTypeOf(
            $matchNode->subject
        );

        return match (true) {
            BooleanType::get()->is($typeOfSubject) => $this->resolveTypeOfBooleanMatch($matchNode),
            $typeOfSubject instanceof EnumType => $this->resolveTypeOfEnumMatch($matchNode, $typeOfSubject),
            default => throw new \Exception('@TODO: Not handled ' . $typeOfSubject::class)
        };
    }
}
