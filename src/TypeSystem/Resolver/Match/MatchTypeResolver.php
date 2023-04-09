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

use PackageFactory\ComponentEngine\Parser\Ast\BooleanLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\MatchNode;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\Expression\ExpressionTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\Type\BooleanType\BooleanType;
use PackageFactory\ComponentEngine\TypeSystem\Type\EnumType\EnumInstanceType;
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

    private function resolveTypeOfEnumMatch(MatchNode $matchNode, EnumInstanceType $subjectEnumType): TypeInterface
    {
        $expressionTypeResolver = new ExpressionTypeResolver(
            scope: $this->scope
        );
        $types = [];

        $defaultArmPresent = false;
        $referencedEnumMembers = [];

        foreach ($matchNode->arms->items as $matchArmNode) {
            if ($defaultArmPresent) {
                throw new \Exception('@TODO Error: Multiple illegal default arms');
            }
            if ($matchArmNode->left === null) {
                $defaultArmPresent = true;
            } else {
                foreach ($matchArmNode->left->items as $expressionNode) {
                    $enumMemberType = $expressionTypeResolver->resolveTypeOf($expressionNode);
                    if (!$enumMemberType instanceof EnumInstanceType) {
                        throw new \Error('@TODO Error: Cannot match enum with type of ' . $enumMemberType::class);
                    }

                    if (!$enumMemberType->enumStaticType->is($subjectEnumType->enumStaticType)) {
                        throw new \Error('@TODO Error: incompatible enum match: got ' . $enumMemberType->enumStaticType->enumName . ' expected ' . $subjectEnumType->enumStaticType->enumName);
                    }

                    if (isset($referencedEnumMembers[$enumMemberType->memberName])) {
                        throw new \Error('@TODO Error: Enum path ' . $enumMemberType->memberName . ' was already defined once in this match and cannot be used twice');
                    }

                    $referencedEnumMembers[$enumMemberType->memberName] = true;
                }
            }

            $types[] = $expressionTypeResolver->resolveTypeOf(
                $matchArmNode->right
            );
        }

        if (!$defaultArmPresent) {
            foreach ($subjectEnumType->enumStaticType->getMemberNames() as $member) {
                if (!isset($referencedEnumMembers[$member])) {
                    throw new \Error('@TODO Error: member ' . $member . ' not checked');
                }
            }
        }

        return UnionType::of(...$types);
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
            $typeOfSubject instanceof EnumInstanceType => $this->resolveTypeOfEnumMatch($matchNode, $typeOfSubject),
            default => throw new \Exception('@TODO: Not handled ' . $typeOfSubject::class)
        };
    }
}
