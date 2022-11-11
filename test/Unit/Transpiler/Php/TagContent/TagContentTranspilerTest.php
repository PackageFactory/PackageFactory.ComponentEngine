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

namespace PackageFactory\ComponentEngine\Test\Unit\Transpiler\Php\TagContent;

use PackageFactory\ComponentEngine\Parser\Ast\ComponentDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\Transpiler\Php\TagContent\TagContentTranspiler;
use PackageFactory\ComponentEngine\TypeSystem\Type\ComponentType\ComponentType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PHPUnit\Framework\TestCase;

final class TagContentTranspilerTest extends TestCase
{
    public function tagContentExamples(): array
    {
        return [
            'Just some text.' => [
                'Just some text.', 
                'Just some text.', 
            ],
            '{someValue}' => [
                '{someValue}',
                '\' . $this->someValue . \'', 
            ],
            '<h1>Headline</h1>' => [
                '<h1>Headline</h1>',
                '<h1>Headline</h1>', 
            ],
        ];
    }

    /**
     * @dataProvider tagContentExamples
     * @test
     * @param string $tagContentAsString
     * @param string $expectedTranspilationResult
     * @return void
     */
    public function transpilesTagContentNodes(string $tagContentAsString, string $expectedTranspilationResult): void
    {
        $tagContentTranspiler = new TagContentTranspiler(
            scope: new DummyScope([
                'someValue' => StringType::get()
            ])
        );
        $tagContentNode = ExpressionNode::fromString(
            sprintf('<div>%s</div>', $tagContentAsString)
        )->root->children->items[0];

        $actualTranspilationResult = $tagContentTranspiler->transpile(
            $tagContentNode
        );

        $this->assertEquals(
            $expectedTranspilationResult,
            $actualTranspilationResult
        );
    }

    /**
     * @test
     * @return void
     */
    public function addsCallToRenderFunctionIfInterpolatedValueIsOfTypeComponent(): void
    {
        $tagContentTranspiler = new TagContentTranspiler(
            scope: new DummyScope([
                'button' => ComponentType::fromComponentDeclarationNode(
                    ComponentDeclarationNode::fromString(
                        'component Button { return <button></button> }'
                    )
                )
            ])
        );
        $tagContentNode = ExpressionNode::fromString(
            '<div>{button}</div>'
        )->root->children->items[0];

        $expectedTranspilationResult = '\' . $this->button->render() . \'';
        $actualTranspilationResult = $tagContentTranspiler->transpile(
            $tagContentNode
        );

        $this->assertEquals(
            $expectedTranspilationResult,
            $actualTranspilationResult
        );
    }
}