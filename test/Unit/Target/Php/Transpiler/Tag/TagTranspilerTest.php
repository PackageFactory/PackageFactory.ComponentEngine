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

namespace PackageFactory\ComponentEngine\Test\Unit\Target\Php\Transpiler\Tag;

use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\TagNode;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\Tag\TagTranspiler;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PHPUnit\Framework\TestCase;

final class TagTranspilerTest extends TestCase
{
    /**
     * @return array<string,mixed>
     */
    public static function tagExamples(): array
    {
        return [
            '<div>Just some text.</div>' => [
                '<div>Just some text.</div>',
                '<div>Just some text.</div>',
            ],
            '<div>Interpolation: {someValue}? Works.</div>' => [
                '<div>Interpolation: {someValue}? Works.</div>',
                '<div>Interpolation: \' . $this->someValue . \'? Works.</div>',
            ],
            '<div>Tag with <strong>inline</strong> markup</div>' => [
                '<div>Tag with <strong>inline</strong> markup</div>',
                '<div>Tag with <strong>inline</strong> markup</div>',
            ],
        ];
    }

    /**
     * @dataProvider tagExamples
     * @test
     * @param string $tagAsString
     * @param string $expectedTranspilationResult
     * @return void
     */
    public function transpilesTagNodes(string $tagAsString, string $expectedTranspilationResult): void
    {
        $tagTranspiler = new TagTranspiler(
            scope: new DummyScope([
                'someValue' => StringType::get()
            ])
        );
        $tagNode = ExpressionNode::fromString($tagAsString)->root;
        assert($tagNode instanceof TagNode);

        $actualTranspilationResult = $tagTranspiler->transpile($tagNode);

        $this->assertEquals(
            $expectedTranspilationResult,
            $actualTranspilationResult
        );
    }
}
