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

namespace PackageFactory\ComponentEngine\Test\Integration;

use PackageFactory\ComponentEngine\Parser\Ast\ExportNode;
use PackageFactory\ComponentEngine\Parser\Ast\Module\Module;
use PackageFactory\ComponentEngine\Parser\Ast\ModuleNode;
use PackageFactory\ComponentEngine\Parser\Ast\Scope;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Type\Enum\EnumMember;
use PackageFactory\ComponentEngine\Type\Enum\EnumMembers;
use PackageFactory\ComponentEngine\Type\Enum\EnumName;
use PackageFactory\ComponentEngine\Type\Enum\EnumType;
use PackageFactory\ComponentEngine\Type\FunctionType;
use PackageFactory\ComponentEngine\Type\Primitive\NumberType;
use PackageFactory\ComponentEngine\Type\Record\RecordEntry;
use PackageFactory\ComponentEngine\Type\Record\RecordType;
use PackageFactory\ComponentEngine\Type\Tuple;
use PackageFactory\ComponentEngine\TypeResolver\Scope\BlockScope;
use PackageFactory\ComponentEngine\TypeResolver\Scope\ModuleScope;
use PackageFactory\ComponentEngine\TypeResolver\TypeResolver;
use PHPUnit\Framework\TestCase;

final class IntegrationTest extends TestCase
{
    public function tokenizationExamples(): array
    {
        return [
            'ArrayStandardApi' => ["ArrayStandardApi"],
            'Comment' => ["Comment"],
            'ComplexStringExpressions' => ["ComplexStringExpressions"],
            'Component' => ["Component"],
            'ComponentWithKeywords' => ["ComponentWithKeywords"],
            'ComponentWithNesting' => ["ComponentWithNesting"],
            'Enum' => ["Enum"],
            'Expression' => ["Expression"],
            'ImportExport' => ["ImportExport"],
            'Interface' => ["Interface"],
            'Match' => ["Match"],
            'Numbers' => ["Numbers"],
            'TemplateLiteral' => ["TemplateLiteral"],
            'TemplateLiteralWithFunctionCallPatterns' => ["TemplateLiteralWithFunctionCallPatterns"],
        ];
    }

    /**
     * @dataProvider tokenizationExamples
     * @test
     * @small
     * @param string $input
     * @return void
     */
    public function testTokenizer(string $example): void
    {
        $source = Source::fromFile(__DIR__ . '/Examples/' . $example . '/' . $example . '.afx');
        $tokenizer = Tokenizer::fromSource($source);
        $expected = json_decode(
            file_get_contents(__DIR__ . '/Examples/' . $example . '/' . $example . '.tokens.json')
        );

        $index = 0;
        foreach ($tokenizer as $token) {
            if (!isset($expected[$index])) {
                $tokenType = $token->type;
                $this->fail("Unfinished expectation at $index [$tokenType->name]($token->value)");
            }
            $this->assertEquals($expected[$index]->type, $token->type->name, "Type mismatch at index $index ($token->value)");
            $this->assertEquals($expected[$index]->value, $token->value, "Value mismatch at index $index");
            $index++;
        }
    }

    public function astExamples(): array
    {
        return [
            'ArrayStandardApi' => ["ArrayStandardApi"],
            'Comment' => ["Comment"],
            'ComplexStringExpressions' => ["ComplexStringExpressions"],
            'Component' => ["Component"],
            'ComponentWithKeywords' => ["ComponentWithKeywords"],
            'ComponentWithNesting' => ["ComponentWithNesting"],
            'Enum' => ["Enum"],
            'Expression' => ["Expression"],
            'ImportExport' => ["ImportExport"],
            'Interface' => ["Interface"],
            'Match' => ["Match"],
            'Numbers' => ["Numbers"],
            'TemplateLiteral' => ["TemplateLiteral"],
            'TemplateLiteralWithFunctionCallPatterns' => ["TemplateLiteralWithFunctionCallPatterns"],
        ];
    }

    /**
     * @dataProvider astExamples
     * @test
     * @small
     * @param string $input
     * @return void
     */
    public function testParser(string $example): void
    {
        $source = Source::fromFile(__DIR__ . '/Examples/' . $example . '/' . $example . '.afx');
        $tokenizer = Tokenizer::fromSource($source);
        $expected = json_decode(
            file_get_contents(__DIR__ . '/Examples/' . $example . '/' . $example . '.ast.json'),
            true
        );

        $module = ModuleNode::fromTokens($tokenizer->getIterator());

        $this->assertEquals($expected, json_decode(json_encode($module), true));
    }

    public function typeResolverExamples(): array
    {
        return [
            'ArrayStandardApi' => ["ArrayStandardApi"],
            'ComplexStringExpressions' => ["ComplexStringExpressions"],
            'Enum' => ["Enum"],
            'Expression' => ["Expression"],
            'Match' => ["Match"],
            'Numbers' => ["Numbers"],
            'TemplateLiteral' => ["TemplateLiteral"],
        ];
    }

    /**
     * @dataProvider typeResolverExamples
     * @test
     * @small
     * @param string $input
     * @return void
     */
    public function testTypeResolver(string $example): void
    {
        $source = Source::fromFile(__DIR__ . '/Examples/' . $example . '/' . $example . '.afx');
        $tokenizer = Tokenizer::fromSource($source);
        $module = ModuleNode::fromTokens($tokenizer->getIterator());
        $typeResolver = new TypeResolver(
            scope: BlockScope::fromRecordType(
                RecordType::of(
                    RecordEntry::of('round', FunctionType::create(
                        Tuple::of(NumberType::create()),
                        NumberType::create()
                    )),
                    RecordEntry::of('ButtonType', EnumType::create(
                        EnumName::fromString('ButtonType'),
                        EnumMembers::of(
                            EnumMember::create("LINK"),
                            EnumMember::create("BUTTON"),
                            EnumMember::create("SUBMIT"),
                            EnumMember::create("NONE")
                        )
                    ))
                )
            )->push(
                ModuleScope::fromModuleNode($module)
            )
        );



        $expected = json_decode(
            file_get_contents(__DIR__ . '/Examples/' . $example . '/' . $example . '.types.json'),
            true
        );

        $this->assertEquals(
            $expected,
            json_decode(json_encode(
                array_map(function (ExportNode $export) use ($typeResolver) {
                    return $typeResolver->getTypedAstForExport($export);
                }, $module->exports->items)
            ), true)
        );
    }
}
