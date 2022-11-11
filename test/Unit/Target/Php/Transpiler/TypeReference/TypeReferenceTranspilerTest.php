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

namespace PackageFactory\ComponentEngine\Test\Unit\Target\Php\Transpiler\TypeReference;

use PackageFactory\ComponentEngine\Parser\Ast\TypeReferenceNode;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\TypeReference\TypeReferenceTranspiler;
use PHPUnit\Framework\TestCase;

final class TypeReferenceTranspilerTest extends TestCase
{
    /**
     * @return array<string,mixed>
     */
    public function primitiveTypeReferenceExamples(): array
    {
        return [
            'string' => ['string', 'string'],
            'boolean' => ['boolean', 'bool'],
            'number' => ['number', 'int|float'],
        ];
    }

    /**
     * @dataProvider primitiveTypeReferenceExamples
     * @test
     * @param string $typeReferenceAsString
     * @param string $expectedTranspilationResult
     * @return void
     */
    public function transpilesReferencesToPrimitiveTypes(string $typeReferenceAsString, string $expectedTranspilationResult): void
    {
        $typeReferenceTranspiler = new TypeReferenceTranspiler();
        $typeReferenceNode = TypeReferenceNode::fromString($typeReferenceAsString);

        $actualTranspilationResult = $typeReferenceTranspiler->transpile(
            $typeReferenceNode
        );

        $this->assertEquals(
            $expectedTranspilationResult,
            $actualTranspilationResult
        );
    }
}