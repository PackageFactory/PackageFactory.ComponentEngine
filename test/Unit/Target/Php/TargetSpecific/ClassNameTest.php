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

namespace PackageFactory\ComponentEngine\Test\Unit\Target\Php\TargetSpecific;

use PackageFactory\ComponentEngine\Target\Php\TargetSpecific\ClassName;
use PHPUnit\Framework\TestCase;

final class ClassNameTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function isFlyweight(): void
    {
        $this->assertSame(
            ClassName::fromString('Vendor\\Site\\SomeClass'),
            ClassName::fromString('Vendor\\Site\\SomeClass')
        );
        $this->assertSame(
            ClassName::fromString('Vendor\\Site\\SomeOtherClass'),
            ClassName::fromString('Vendor\\Site\\SomeOtherClass')
        );
        $this->assertSame(
            ClassName::fromString('OtherVendor\\OtherSite\\SomeClass'),
            ClassName::fromString('OtherVendor\\OtherSite\\SomeClass')
        );
    }

    /**
     * @return array
     */
    public static function invalidClassNameExamples(): array
    {
        return [
            'I am not a valid class name' => ['I am not a valid class name'],
            '1AmNotAValidClassName' => ['1AmNotAValidClassName'],
            'int' => ['int'],
            'float' => ['float'],
            'bool' => ['bool'],
            'string' => ['string'],
            'true' => ['true'],
            'false' => ['false'],
            'null' => ['null'],
            'void' => ['void'],
            'iterable' => ['iterable'],
            'object' => ['object'],
            'mixed' => ['mixed'],
            'never' => ['never'],
            'enum' => ['enum'],
            'resource' => ['resource'],
            'numeric' => ['numeric'],
            '__halt_compiler' => ['__halt_compiler'],
            'abstract' => ['abstract'],
            'and' => ['and'],
            'array' => ['array'],
            'as' => ['as'],
            'break' => ['break'],
            'callable' => ['callable'],
            'case' => ['case'],
            'catch' => ['catch'],
            'class' => ['class'],
            'clone' => ['clone'],
            'const' => ['const'],
            'continue' => ['continue'],
            'declare' => ['declare'],
            'default' => ['default'],
            'die' => ['die'],
            'do' => ['do'],
            'echo' => ['echo'],
            'else' => ['else'],
            'elseif' => ['elseif'],
            'empty' => ['empty'],
            'enddeclare' => ['enddeclare'],
            'endfor' => ['endfor'],
            'endforeach' => ['endforeach'],
            'endif' => ['endif'],
            'endswitch' => ['endswitch'],
            'endwhile' => ['endwhile'],
            'eval' => ['eval'],
            'exit' => ['exit'],
            'extends' => ['extends'],
            'final' => ['final'],
            'finally' => ['finally'],
            'fn' => ['fn'],
            'for' => ['for'],
            'foreach' => ['foreach'],
            'function' => ['function'],
            'global' => ['global'],
            'goto' => ['goto'],
            'if' => ['if'],
            'implements' => ['implements'],
            'include' => ['include'],
            'include_once' => ['include_once'],
            'instanceof' => ['instanceof'],
            'insteadof' => ['insteadof'],
            'interface' => ['interface'],
            'isset' => ['isset'],
            'list' => ['list'],
            'match' => ['match'],
            'namespace' => ['namespace'],
            'new' => ['new'],
            'or' => ['or'],
            'print' => ['print'],
            'private' => ['private'],
            'protected' => ['protected'],
            'public' => ['public'],
            'readonly' => ['readonly'],
            'require' => ['require'],
            'require_once' => ['require_once'],
            'return' => ['return'],
            'static' => ['static'],
            'switch' => ['switch'],
            'throw' => ['throw'],
            'trait' => ['trait'],
            'try' => ['try'],
            'unset' => ['unset'],
            'use' => ['use'],
            'var' => ['var'],
            'while' => ['while'],
            'xor' => ['xor'],
            'yield' => ['yield'],
            '__class__' => ['__class__'],
            '__dir__' => ['__dir__'],
            '__file__' => ['__file__'],
            '__function__' => ['__function__'],
            '__line__' => ['__line__'],
            '__method__' => ['__method__'],
            '__namespace__' => ['__namespace__'],
            '__trait__' => ['__trait__'],
            'INT' => ['INT'],
            'FLOAT' => ['FLOAT'],
            'BOOL' => ['BOOL'],
            'STRING' => ['STRING'],
            'TRUE' => ['TRUE'],
            'FALSE' => ['FALSE'],
            'NULL' => ['NULL'],
            'VOID' => ['VOID'],
            'ITERABLE' => ['ITERABLE'],
            'OBJECT' => ['OBJECT'],
            'MIXED' => ['MIXED'],
            'NEVER' => ['NEVER'],
            'ENUM' => ['ENUM'],
            'RESOURCE' => ['RESOURCE'],
            'NUMERIC' => ['NUMERIC'],
            '__HALT_COMPILER' => ['__HALT_COMPILER'],
            'ABSTRACT' => ['ABSTRACT'],
            'AND' => ['AND'],
            'ARRAY' => ['ARRAY'],
            'AS' => ['AS'],
            'BREAK' => ['BREAK'],
            'CALLABLE' => ['CALLABLE'],
            'CASE' => ['CASE'],
            'CATCH' => ['CATCH'],
            'CLASS' => ['CLASS'],
            'CLONE' => ['CLONE'],
            'CONST' => ['CONST'],
            'CONTINUE' => ['CONTINUE'],
            'DECLARE' => ['DECLARE'],
            'DEFAULT' => ['DEFAULT'],
            'DIE' => ['DIE'],
            'DO' => ['DO'],
            'ECHO' => ['ECHO'],
            'ELSE' => ['ELSE'],
            'ELSEIF' => ['ELSEIF'],
            'EMPTY' => ['EMPTY'],
            'ENDDECLARE' => ['ENDDECLARE'],
            'ENDFOR' => ['ENDFOR'],
            'ENDFOREACH' => ['ENDFOREACH'],
            'ENDIF' => ['ENDIF'],
            'ENDSWITCH' => ['ENDSWITCH'],
            'ENDWHILE' => ['ENDWHILE'],
            'EVAL' => ['EVAL'],
            'EXIT' => ['EXIT'],
            'EXTENDS' => ['EXTENDS'],
            'FINAL' => ['FINAL'],
            'FINALLY' => ['FINALLY'],
            'FN' => ['FN'],
            'FOR' => ['FOR'],
            'FOREACH' => ['FOREACH'],
            'FUNCTION' => ['FUNCTION'],
            'GLOBAL' => ['GLOBAL'],
            'GOTO' => ['GOTO'],
            'IF' => ['IF'],
            'IMPLEMENTS' => ['IMPLEMENTS'],
            'INCLUDE' => ['INCLUDE'],
            'INCLUDE_ONCE' => ['INCLUDE_ONCE'],
            'INSTANCEOF' => ['INSTANCEOF'],
            'INSTEADOF' => ['INSTEADOF'],
            'INTERFACE' => ['INTERFACE'],
            'ISSET' => ['ISSET'],
            'LIST' => ['LIST'],
            'MATCH' => ['MATCH'],
            'NAMESPACE' => ['NAMESPACE'],
            'NEW' => ['NEW'],
            'OR' => ['OR'],
            'PRINT' => ['PRINT'],
            'PRIVATE' => ['PRIVATE'],
            'PROTECTED' => ['PROTECTED'],
            'PUBLIC' => ['PUBLIC'],
            'READONLY' => ['READONLY'],
            'REQUIRE' => ['REQUIRE'],
            'REQUIRE_ONCE' => ['REQUIRE_ONCE'],
            'RETURN' => ['RETURN'],
            'STATIC' => ['STATIC'],
            'SWITCH' => ['SWITCH'],
            'THROW' => ['THROW'],
            'TRAIT' => ['TRAIT'],
            'TRY' => ['TRY'],
            'UNSET' => ['UNSET'],
            'USE' => ['USE'],
            'VAR' => ['VAR'],
            'WHILE' => ['WHILE'],
            'XOR' => ['XOR'],
            'YIELD' => ['YIELD'],
            '__CLASS__' => ['__CLASS__'],
            '__DIR__' => ['__DIR__'],
            '__FILE__' => ['__FILE__'],
            '__FUNCTION__' => ['__FUNCTION__'],
            '__LINE__' => ['__LINE__'],
            '__METHOD__' => ['__METHOD__'],
            '__NAMESPACE__' => ['__NAMESPACE__'],
            '__TRAIT__' => ['__TRAIT__'],
        ];
    }

    /**
     * @dataProvider invalidClassNameExamples
     * @test
     * @param string $invalidClassName
     * @return void
     */
    public function ensuresValidClassName(string $invalidClassName): void
    {
        $this->expectExceptionMessageMatches('/invalid class name/i');

        ClassName::fromString($invalidClassName);
    }

    /**
     * @test
     * @return void
     */
    public function providesFullyQualifiedClassName(): void
    {
        $className = ClassName::fromString('Vendor\\Site\\SomeClass');
        $this->assertEquals(
            'Vendor\\Site\\SomeClass',
            $className->getFullyQualifiedClassName()
        );
    }

    /**
     * @test
     * @return void
     */
    public function providesNamespace(): void
    {
        $className = ClassName::fromString('Vendor\\Site\\SomeClass');
        $this->assertEquals(
            'Vendor\\Site',
            $className->getNamespace()
        );
    }

    /**
     * @test
     * @return void
     */
    public function providesShortClassName(): void
    {
        $className = ClassName::fromString('Vendor\\Site\\SomeClass');
        $this->assertEquals(
            'SomeClass',
            $className->getShortClassName()
        );
    }
}
