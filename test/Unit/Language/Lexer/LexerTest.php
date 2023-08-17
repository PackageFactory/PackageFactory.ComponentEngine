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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Lexer;

use PackageFactory\ComponentEngine\Language\Lexer\Lexer;
use PackageFactory\ComponentEngine\Language\Lexer\LexerException;
use PackageFactory\ComponentEngine\Language\Lexer\Rule\Rule;
use PackageFactory\ComponentEngine\Language\Lexer\Rule\Rules;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PHPUnit\Framework\TestCase;

final class LexerTest extends TestCase
{
    private Lexer $lexer;

    protected function assertLexerState(
        Position $startPosition,
        Position $endPosition,
        string $buffer,
        bool $isEnd
    ): void {
        $this->assertEquals(
            $startPosition,
            $this->lexer->buffer->getStart(),
            'Failed asserting that start position of lexer equals'
        );

        $this->assertEquals(
            $endPosition,
            $this->lexer->buffer->getEnd(),
            'Failed asserting that end position of lexer equals'
        );

        $this->assertEquals(
            $buffer,
            $this->lexer->buffer->getContents(),
            'Failed asserting that buffer of lexer equals'
        );

        $this->assertEquals(
            $isEnd,
            $this->lexer->isEnd(),
            'Failed asserting that isEnd of lexer equals'
        );
    }

    protected function assertThrowsLexerException(callable $fn, LexerException $expectedLexerException): void
    {
        $this->expectExceptionObject($expectedLexerException);

        try {
            $fn();
        } catch (LexerException $e) {
            $this->assertEquals($expectedLexerException, $e);
            throw $e;
        }
    }

    /**
     * @return iterable<mixed>
     */
    public static function singleTokenExamples(): iterable
    {
        yield ($source = '#') =>
            [$source, Rule::COMMENT];
        yield ($source = '# This is a comment') =>
            [$source, Rule::COMMENT];
        yield ($source = '# 🌵🆚⌚️: Multi-byte characters are not a problem inside a comment.') =>
            [$source, Rule::COMMENT];

        yield ($source = 'from') =>
            [$source, Rule::KEYWORD_FROM];
        yield ($source = 'import') =>
            [$source, Rule::KEYWORD_IMPORT];
        yield ($source = 'export') =>
            [$source, Rule::KEYWORD_EXPORT];
        yield ($source = 'enum') =>
            [$source, Rule::KEYWORD_ENUM];
        yield ($source = 'struct') =>
            [$source, Rule::KEYWORD_STRUCT];
        yield ($source = 'component') =>
            [$source, Rule::KEYWORD_COMPONENT];
        yield ($source = 'match') =>
            [$source, Rule::KEYWORD_MATCH];
        yield ($source = 'default') =>
            [$source, Rule::KEYWORD_DEFAULT];
        yield ($source = 'return') =>
            [$source, Rule::KEYWORD_RETURN];
        yield ($source = 'true') =>
            [$source, Rule::KEYWORD_TRUE];
        yield ($source = 'false') =>
            [$source, Rule::KEYWORD_FALSE];
        yield ($source = 'null') =>
            [$source, Rule::KEYWORD_NULL];

        yield ($source = '"') =>
            [$source, Rule::STRING_LITERAL_DELIMITER];
        yield ($source = 'Some string without any escapes') =>
            [$source, Rule::STRING_LITERAL_CONTENT];
        yield ($source = '🌵🆚⌚️: Multi-byte characters are not a problem inside a string.') =>
            [$source, Rule::STRING_LITERAL_CONTENT];

        yield ($source = '0b1001') =>
            [$source, Rule::INTEGER_BINARY];
        yield ($source = '0o12345670') =>
            [$source, Rule::INTEGER_OCTAL];
        yield ($source = '1234567890') =>
            [$source, Rule::INTEGER_DECIMAL];
        yield ($source = '0xABCDEF1234567890') =>
            [$source, Rule::INTEGER_HEXADECIMAL];

        yield ($source = '"""') =>
            [$source, Rule::TEMPLATE_LITERAL_DELIMITER];
        yield ($source = 'Some string without escapes') =>
            [$source, Rule::TEMPLATE_LITERAL_CONTENT];
        yield ($source = '🌵🆚⌚️: Multi-byte characters are not a problem inside of template literals.') =>
            [$source, Rule::TEMPLATE_LITERAL_CONTENT];

        yield ($source = '\\\\') =>
            [$source, Rule::ESCAPE_SEQUENCE_SINGLE_CHARACTER];
        yield ($source = '\\n') =>
            [$source, Rule::ESCAPE_SEQUENCE_SINGLE_CHARACTER];
        yield ($source = '\\t') =>
            [$source, Rule::ESCAPE_SEQUENCE_SINGLE_CHARACTER];
        yield ($source = '\\xA9') =>
            [$source, Rule::ESCAPE_SEQUENCE_HEXADECIMAL];
        yield ($source = '\\u00A9') =>
            [$source, Rule::ESCAPE_SEQUENCE_UNICODE];
        yield ($source = '\\u{2F804}') =>
            [$source, Rule::ESCAPE_SEQUENCE_UNICODE_CODEPOINT];

        yield ($source = '{') =>
            [$source, Rule::BRACKET_CURLY_OPEN];
        yield ($source = '}') =>
            [$source, Rule::BRACKET_CURLY_CLOSE];
        yield ($source = '(') =>
            [$source, Rule::BRACKET_ROUND_OPEN];
        yield ($source = ')') =>
            [$source, Rule::BRACKET_ROUND_CLOSE];
        yield ($source = '[') =>
            [$source, Rule::BRACKET_SQUARE_OPEN];
        yield ($source = ']') =>
            [$source, Rule::BRACKET_SQUARE_CLOSE];
        yield ($source = '<') =>
            [$source, Rule::BRACKET_ANGLE_OPEN];
        yield ($source = '>') =>
            [$source, Rule::BRACKET_ANGLE_CLOSE];

        yield ($source = '.') =>
            [$source, Rule::SYMBOL_PERIOD];
        yield ($source = ':') =>
            [$source, Rule::SYMBOL_COLON];
        yield ($source = '?') =>
            [$source, Rule::SYMBOL_QUESTIONMARK];
        yield ($source = '!') =>
            [$source, Rule::SYMBOL_EXCLAMATIONMARK];
        yield ($source = ',') =>
            [$source, Rule::SYMBOL_COMMA];
        yield ($source = '-') =>
            [$source, Rule::SYMBOL_DASH];
        yield ($source = '=') =>
            [$source, Rule::SYMBOL_EQUALS];
        yield ($source = '/') =>
            [$source, Rule::SYMBOL_SLASH_FORWARD];
        yield ($source = '|') =>
            [$source, Rule::SYMBOL_PIPE];
        yield ($source = '&&') =>
            [$source, Rule::SYMBOL_BOOLEAN_AND];
        yield ($source = '||') =>
            [$source, Rule::SYMBOL_BOOLEAN_OR];
        yield ($source = '===') =>
            [$source, Rule::SYMBOL_STRICT_EQUALS];
        yield ($source = '!==') =>
            [$source, Rule::SYMBOL_NOT_EQUALS];
        yield ($source = '>=') =>
            [$source, Rule::SYMBOL_GREATER_THAN_OR_EQUAL];
        yield ($source = '<=') =>
            [$source, Rule::SYMBOL_LESS_THAN_OR_EQUAL];
        yield ($source = '->') =>
            [$source, Rule::SYMBOL_ARROW_SINGLE];
        yield ($source = '?.') =>
            [$source, Rule::SYMBOL_OPTCHAIN];
        yield ($source = '??') =>
            [$source, Rule::SYMBOL_NULLISH_COALESCE];

        yield ($source = 'ValidWord') =>
            [$source, Rule::WORD];
        yield ($source = 'V4l1dW0rd') =>
            [$source, Rule::WORD];
        yield ($source = '1245ValidWord') =>
            [$source, Rule::WORD];

        yield ($source = 'JustSomeText.TextTerminates-Only-At??Space//Characters.') =>
            [$source, Rule::TEXT];
        yield ($source = '🌵🆚⌚️') =>
            [$source, Rule::TEXT];

        yield ($source = ' ') =>
            [$source, Rule::SPACE];
        yield ($source = '    ') =>
            [$source, Rule::SPACE];
        yield ($source = "\t") =>
            [$source, Rule::SPACE];
        yield ($source = "\t\t\t\t") =>
            [$source, Rule::SPACE];
        yield ($source = " \t \t \t \t ") =>
            [$source, Rule::SPACE];

        yield ($source = "\n") =>
            [$source, Rule::END_OF_LINE];
    }

    /**
     * @dataProvider singleTokenExamples
     * @test
     * @param string $source
     * @param Rule $expectedRule
     * @return void
     */
    public function readSavesTokenOfGivenTypeIfMatchIsFound(string $source, Rule $expectedRule): void
    {
        $this->lexer = new Lexer($source);
        $this->lexer->read($expectedRule);

        $this->assertLexerState(
            startPosition: Position::from(0, 0),
            endPosition: Position::from(0, \mb_strlen($source) - 1),
            buffer: $source,
            isEnd: true
        );
    }

    /**
     * @dataProvider singleTokenExamples
     * @test
     * @param string $source
     * @param Rule $expectedRule
     * @return void
     */
    public function readOneOfSavesTokenOfGivenTypeIfMatchIsFound(string $source, Rule $expectedRule): void
    {
        $this->lexer = new Lexer($source);
        $this->lexer->readOneOf(Rules::from($expectedRule));

        $this->assertLexerState(
            startPosition: Position::from(0, 0),
            endPosition: Position::from(0, \mb_strlen($source) - 1),
            buffer: $source,
            isEnd: true
        );
    }

    /**
     * @return iterable<string,array{string,Rules,array{array{int,int},array{int,int},Rule,string}}>
     */
    public static function multipleTokensExamples(): iterable
    {
        yield ($source = "# This is a comment\n# This is also a comment") => [
            $source,
            Rules::from(Rule::COMMENT, Rule::END_OF_LINE),
            [[0,  0], [0, 18], Rule::COMMENT, '# This is a comment'],
            [[0, 19], [0, 19], Rule::END_OF_LINE, "\n"],
            [[1,  0], [1, 23], Rule::COMMENT, '# This is also a comment'],
        ];

        yield ($source = "1765224, -0xAB89CD, true\nnull") => [
            $source,
            Rules::from(
                Rule::SYMBOL_DASH,
                Rule::SYMBOL_COMMA,
                Rule::INTEGER_HEXADECIMAL,
                Rule::INTEGER_DECIMAL,
                Rule::SPACE,
                Rule::END_OF_LINE,
                Rule::KEYWORD_TRUE,
                Rule::KEYWORD_NULL
            ),
            [[0,  0], [0,  6], Rule::INTEGER_DECIMAL, '1765224'],
            [[0,  7], [0,  7], Rule::SYMBOL_COMMA, ','],
            [[0,  8], [0,  8], Rule::SPACE, ' '],
            [[0,  9], [0,  9], Rule::SYMBOL_DASH, '-'],
            [[0, 10], [0, 17], Rule::INTEGER_HEXADECIMAL, '0xAB89CD'],
            [[0, 18], [0, 18], Rule::SYMBOL_COMMA, ','],
            [[0, 19], [0, 19], Rule::SPACE, ' '],
            [[0, 20], [0, 23], Rule::KEYWORD_TRUE, 'true'],
            [[0, 24], [0, 24], Rule::END_OF_LINE, "\n"],
            [[1,  0], [1,  3], Rule::KEYWORD_NULL, 'null'],
        ];

        yield ($source = '0b100101 892837 0xFFAAEE 0o75374') => [
            $source,
            Rules::from(
                Rule::INTEGER_BINARY,
                Rule::INTEGER_OCTAL,
                Rule::INTEGER_HEXADECIMAL,
                Rule::INTEGER_DECIMAL,
                Rule::SPACE
            ),
            [[0,  0], [0,  7], Rule::INTEGER_BINARY, '0b100101'],
            [[0,  8], [0,  8], Rule::SPACE, ' '],
            [[0,  9], [0, 14], Rule::INTEGER_DECIMAL, '892837'],
            [[0, 15], [0, 15], Rule::SPACE, ' '],
            [[0, 16], [0, 23], Rule::INTEGER_HEXADECIMAL, '0xFFAAEE'],
            [[0, 24], [0, 24], Rule::SPACE, ' '],
            [[0, 25], [0, 31], Rule::INTEGER_OCTAL, '0o75374'],
        ];

        yield ($source = '"This is a string literal with \\n escapes \\xB1 \\u5FA9 \\u{1343E}!"') => [
            $source,
            Rules::from(
                Rule::STRING_LITERAL_DELIMITER,
                Rule::STRING_LITERAL_CONTENT,
                Rule::ESCAPE_SEQUENCE_SINGLE_CHARACTER,
                Rule::ESCAPE_SEQUENCE_HEXADECIMAL,
                Rule::ESCAPE_SEQUENCE_UNICODE,
                Rule::ESCAPE_SEQUENCE_UNICODE_CODEPOINT
            ),
            [[0,  0], [0,  0], Rule::STRING_LITERAL_DELIMITER, '"'],
            [[0,  1], [0, 30], Rule::STRING_LITERAL_CONTENT, 'This is a string literal with '],
            [[0, 31], [0, 32], Rule::ESCAPE_SEQUENCE_SINGLE_CHARACTER, '\\n'],
            [[0, 33], [0, 41], Rule::STRING_LITERAL_CONTENT, ' escapes '],
            [[0, 42], [0, 45], Rule::ESCAPE_SEQUENCE_HEXADECIMAL, '\\xB1'],
            [[0, 46], [0, 46], Rule::STRING_LITERAL_CONTENT, ' '],
            [[0, 47], [0, 52], Rule::ESCAPE_SEQUENCE_UNICODE, '\\u5FA9'],
            [[0, 53], [0, 53], Rule::STRING_LITERAL_CONTENT, ' '],
            [[0, 54], [0, 62], Rule::ESCAPE_SEQUENCE_UNICODE_CODEPOINT, '\\u{1343E}'],
            [[0, 63], [0, 63], Rule::STRING_LITERAL_CONTENT, '!'],
            [[0, 64], [0, 64], Rule::STRING_LITERAL_DELIMITER, '"']
        ];

        $source = <<<AFX
        """
            This is "template literal" content with \\n escapes \\xB1 \\u5FA9 \\u{1343E}
            and embedded expressions: {} :)
            """
        AFX;
        yield $source => [
            $source,
            Rules::from(
                Rule::TEMPLATE_LITERAL_DELIMITER,
                Rule::SPACE,
                Rule::TEMPLATE_LITERAL_CONTENT,
                Rule::ESCAPE_SEQUENCE_SINGLE_CHARACTER,
                Rule::ESCAPE_SEQUENCE_HEXADECIMAL,
                Rule::ESCAPE_SEQUENCE_UNICODE,
                Rule::ESCAPE_SEQUENCE_UNICODE_CODEPOINT,
                Rule::END_OF_LINE,
                Rule::BRACKET_CURLY_OPEN,
                Rule::BRACKET_CURLY_CLOSE
            ),
            [[0,  0], [0,  2], Rule::TEMPLATE_LITERAL_DELIMITER, '"""'],
            [[0,  3], [0,  3], Rule::END_OF_LINE, "\n"],
            [[1,  0], [1,  3], Rule::SPACE, '    '],
            [[1,  4], [1, 43], Rule::TEMPLATE_LITERAL_CONTENT, 'This is "template literal" content with '],
            [[1, 44], [1, 45], Rule::ESCAPE_SEQUENCE_SINGLE_CHARACTER, '\\n'],
            [[1, 46], [1, 46], Rule::SPACE, ' '],
            [[1, 47], [1, 54], Rule::TEMPLATE_LITERAL_CONTENT, 'escapes '],
            [[1, 55], [1, 58], Rule::ESCAPE_SEQUENCE_HEXADECIMAL, '\\xB1'],
            [[1, 59], [1, 59], Rule::SPACE, ' '],
            [[1, 60], [1, 65], Rule::ESCAPE_SEQUENCE_UNICODE, '\\u5FA9'],
            [[1, 66], [1, 66], Rule::SPACE, ' '],
            [[1, 67], [1, 75], Rule::ESCAPE_SEQUENCE_UNICODE_CODEPOINT, '\\u{1343E}'],
            [[1, 76], [1, 76], Rule::END_OF_LINE, "\n"],
            [[2,  0], [2,  3], Rule::SPACE, '    '],
            [[2,  4], [2, 29], Rule::TEMPLATE_LITERAL_CONTENT, 'and embedded expressions: '],
            [[2, 30], [2, 30], Rule::BRACKET_CURLY_OPEN, '{'],
            [[2, 31], [2, 31], Rule::BRACKET_CURLY_CLOSE, '}'],
            [[2, 32], [2, 32], Rule::SPACE, ' '],
            [[2, 33], [2, 34], Rule::TEMPLATE_LITERAL_CONTENT, ':)'],
            [[2, 35], [2, 35], Rule::END_OF_LINE, "\n"],
            [[3,  0], [3,  3], Rule::SPACE, '    '],
            [[3,  4], [3,  6], Rule::TEMPLATE_LITERAL_DELIMITER, '"""'],
        ];

        $source = <<<AFX
        <a href="">
            <my-element/>
            <div class={} foo:bar>
            </div>
        </a>
        AFX;
        yield $source => [
            $source,
            Rules::from(
                Rule::BRACKET_ANGLE_OPEN,
                Rule::WORD,
                Rule::SPACE,
                Rule::SYMBOL_EQUALS,
                Rule::STRING_LITERAL_DELIMITER,
                Rule::BRACKET_ANGLE_CLOSE,
                Rule::END_OF_LINE,
                Rule::SYMBOL_DASH,
                Rule::SYMBOL_SLASH_FORWARD,
                Rule::BRACKET_CURLY_OPEN,
                Rule::BRACKET_CURLY_CLOSE,
                Rule::SYMBOL_COLON
            ),
            [[0,  0], [0,  0], Rule::BRACKET_ANGLE_OPEN, '<'],
            [[0,  1], [0,  1], Rule::WORD, 'a'],
            [[0,  2], [0,  2], Rule::SPACE, ' '],
            [[0,  3], [0,  6], Rule::WORD, 'href'],
            [[0,  7], [0,  7], Rule::SYMBOL_EQUALS, '='],
            [[0,  8], [0,  8], Rule::STRING_LITERAL_DELIMITER, '"'],
            [[0,  9], [0,  9], Rule::STRING_LITERAL_DELIMITER, '"'],
            [[0, 10], [0, 10], Rule::BRACKET_ANGLE_CLOSE, '>'],
            [[0, 11], [0, 11], Rule::END_OF_LINE, "\n"],
            [[1,  0], [1,  3], Rule::SPACE, '    '],
            [[1,  4], [1,  4], Rule::BRACKET_ANGLE_OPEN, '<'],
            [[1,  5], [1,  6], Rule::WORD, 'my'],
            [[1,  7], [1,  7], Rule::SYMBOL_DASH, '-'],
            [[1,  8], [1, 14], Rule::WORD, 'element'],
            [[1, 15], [1, 15], Rule::SYMBOL_SLASH_FORWARD, '/'],
            [[1, 16], [1, 16], Rule::BRACKET_ANGLE_CLOSE, '>'],
            [[1, 17], [1, 17], Rule::END_OF_LINE, "\n"],
            [[2,  0], [2,  3], Rule::SPACE, '    '],
            [[2,  4], [2,  4], Rule::BRACKET_ANGLE_OPEN, '<'],
            [[2,  5], [2,  7], Rule::WORD, 'div'],
            [[2,  8], [2,  8], Rule::SPACE, ' '],
            [[2,  9], [2, 13], Rule::WORD, 'class'],
            [[2, 14], [2, 14], Rule::SYMBOL_EQUALS, '='],
            [[2, 15], [2, 15], Rule::BRACKET_CURLY_OPEN, '{'],
            [[2, 16], [2, 16], Rule::BRACKET_CURLY_CLOSE, '}'],
            [[2, 17], [2, 17], Rule::SPACE, ' '],
            [[2, 18], [2, 20], Rule::WORD, 'foo'],
            [[2, 21], [2, 21], Rule::SYMBOL_COLON, ':'],
            [[2, 22], [2, 24], Rule::WORD, 'bar'],
            [[2, 25], [2, 25], Rule::BRACKET_ANGLE_CLOSE, '>'],
            [[2, 26], [2, 26], Rule::END_OF_LINE, "\n"],
            [[3,  0], [3,  3], Rule::SPACE, '    '],
            [[3,  4], [3,  4], Rule::BRACKET_ANGLE_OPEN, '<'],
            [[3,  5], [3,  5], Rule::SYMBOL_SLASH_FORWARD, '/'],
            [[3,  6], [3,  8], Rule::WORD, 'div'],
            [[3,  9], [3,  9], Rule::BRACKET_ANGLE_CLOSE, '>'],
            [[3, 10], [3, 10], Rule::END_OF_LINE, "\n"],
            [[4,  0], [4,  0], Rule::BRACKET_ANGLE_OPEN, '<'],
            [[4,  1], [4,  1], Rule::SYMBOL_SLASH_FORWARD, '/'],
            [[4,  2], [4,  2], Rule::WORD, 'a'],
            [[4,  3], [4,  3], Rule::BRACKET_ANGLE_CLOSE, '>'],
        ];

        $source = <<<AFX
        ThisIsSomeText-with-expressions{}
        line-breaks,   spaces   andTags<>inside.
        AFX;
        yield $source => [
            $source,
            Rules::from(
                Rule::TEXT,
                Rule::BRACKET_CURLY_OPEN,
                Rule::BRACKET_CURLY_CLOSE,
                Rule::SPACE,
                Rule::END_OF_LINE,
                Rule::BRACKET_ANGLE_OPEN,
                Rule::BRACKET_ANGLE_CLOSE
            ),
            [[0,  0], [0, 30], Rule::TEXT, 'ThisIsSomeText-with-expressions'],
            [[0, 31], [0, 31], Rule::BRACKET_CURLY_OPEN, '{'],
            [[0, 32], [0, 32], Rule::BRACKET_CURLY_CLOSE, '}'],
            [[0, 33], [0, 33], Rule::END_OF_LINE, "\n"],
            [[1,  0], [1, 11], Rule::TEXT, 'line-breaks,'],
            [[1, 12], [1, 14], Rule::SPACE, '   '],
            [[1, 15], [1, 20], Rule::TEXT, 'spaces'],
            [[1, 21], [1, 23], Rule::SPACE, '   '],
            [[1, 24], [1, 30], Rule::TEXT, 'andTags'],
            [[1, 31], [1, 31], Rule::BRACKET_ANGLE_OPEN, '<'],
            [[1, 32], [1, 32], Rule::BRACKET_ANGLE_CLOSE, '>'],
            [[1, 33], [1, 39], Rule::TEXT, 'inside.'],
        ];
    }

    /**
     * @dataProvider multipleTokensExamples
     * @test
     * @param string $source
     * @param array{array{int,int},array{int,int},Rule,string} ...$expectedLexerStates
     * @return void
     */
    public function testReadOneOfWithMultipleRules(
        string $source,
        Rules $rules,
        array ...$expectedLexerStates
    ): void {
        $this->lexer = new Lexer($source);

        foreach ($expectedLexerStates as $i => $expectedLexerState) {
            $this->lexer->readOneOf($rules);

            $this->assertLexerState(
                startPosition: Position::from(...$expectedLexerState[0]),
                endPosition: Position::from(...$expectedLexerState[1]),
                buffer: $expectedLexerState[3],
                isEnd: $i === count($expectedLexerStates) - 1
            );
        }
    }

    /**
     * @return iterable<mixed>
     */
    public static function failingSingleTokenExamples(): iterable
    {
        $example = static function (
            Rule $type,
            string $source,
            string $unexpectedCharacterSequence
        ) {
            yield sprintf('%s: %s', $type->value, $source) => [
                $source,
                $type,
                Range::from(
                    Position::from(0, 0),
                    Position::from(0, \mb_strlen($unexpectedCharacterSequence) - 1),
                ),
                $unexpectedCharacterSequence
            ];
        };

        yield from $example(Rule::COMMENT, 'Anything that does not start with "#"', 'A');

        yield from $example(Rule::KEYWORD_FROM, 'false', 'fa');
        yield from $example(Rule::KEYWORD_IMPORT, 'implausible', 'impl');
        yield from $example(Rule::KEYWORD_EXPORT, 'ex-machina', 'ex-');
        yield from $example(Rule::KEYWORD_ENUM, 'enough', 'eno');
        yield from $example(Rule::KEYWORD_STRUCT, 'strict', 'stri');
        yield from $example(Rule::KEYWORD_COMPONENT, 'composition', 'compos');
        yield from $example(Rule::KEYWORD_MATCH, 'matter', 'matt');
        yield from $example(Rule::KEYWORD_DEFAULT, 'definition', 'defi');
        yield from $example(Rule::KEYWORD_RETURN, 'retroactive', 'retr');
        yield from $example(Rule::KEYWORD_TRUE, 'truth', 'trut');
        yield from $example(Rule::KEYWORD_FALSE, 'falsify', 'falsi');
        yield from $example(Rule::KEYWORD_NULL, 'nuclear', 'nuc');

        yield from $example(Rule::STRING_LITERAL_DELIMITER, '\'', '\'');
        yield from $example(Rule::STRING_LITERAL_CONTENT, '"', '"');
        yield from $example(Rule::STRING_LITERAL_CONTENT, '\\', '\\');

        yield from $example(Rule::INTEGER_BINARY, '001001', '00');
        yield from $example(Rule::INTEGER_BINARY, '0b21', '0b2');
        yield from $example(Rule::INTEGER_OCTAL, '0p12345670', '0p');
        yield from $example(Rule::INTEGER_OCTAL, '0o84', '0o8');
        yield from $example(Rule::INTEGER_DECIMAL, ' ', ' ');
        yield from $example(Rule::INTEGER_DECIMAL, 'foo', 'f');
        yield from $example(Rule::INTEGER_HEXADECIMAL, '0xG', '0xG');
        yield from $example(Rule::INTEGER_HEXADECIMAL, '0yFFAA00', '0y');

        yield from $example(Rule::TEMPLATE_LITERAL_DELIMITER, '`', '`');
        yield from $example(Rule::TEMPLATE_LITERAL_CONTENT, '{', '{');
        yield from $example(Rule::TEMPLATE_LITERAL_CONTENT, '}', '}');
        yield from $example(Rule::TEMPLATE_LITERAL_CONTENT, "\n", "\n");
        yield from $example(Rule::TEMPLATE_LITERAL_CONTENT, '\\', '\\');

        yield from $example(Rule::ESCAPE_SEQUENCE_SINGLE_CHARACTER, '\\o', '\\o');
        yield from $example(Rule::ESCAPE_SEQUENCE_SINGLE_CHARACTER, '\\ü', '\\ü');
        yield from $example(Rule::ESCAPE_SEQUENCE_SINGLE_CHARACTER, '\\£', '\\£');
        yield from $example(Rule::ESCAPE_SEQUENCE_SINGLE_CHARACTER, '\\И', '\\И');
        yield from $example(Rule::ESCAPE_SEQUENCE_SINGLE_CHARACTER, '\\ह', '\\ह');
        yield from $example(Rule::ESCAPE_SEQUENCE_SINGLE_CHARACTER, '\\€', '\\€');
        yield from $example(Rule::ESCAPE_SEQUENCE_SINGLE_CHARACTER, '\\𐍈', '\\𐍈');
        yield from $example(Rule::ESCAPE_SEQUENCE_SINGLE_CHARACTER, '\\8', '\\8');
        yield from $example(Rule::ESCAPE_SEQUENCE_HEXADECIMAL, '\\x9G', '\\x9G');
        yield from $example(Rule::ESCAPE_SEQUENCE_UNICODE, '\\u00AY', '\\u00AY');
        yield from $example(Rule::ESCAPE_SEQUENCE_UNICODE_CODEPOINT, '\\u{2F8O4}', '\\u{2F8O');

        yield from $example(Rule::BRACKET_CURLY_OPEN, 'a', 'a');
        yield from $example(Rule::BRACKET_CURLY_OPEN, '😱', '😱');
        yield from $example(Rule::BRACKET_CURLY_CLOSE, 'b', 'b');
        yield from $example(Rule::BRACKET_CURLY_CLOSE, '🖖', '🖖');
        yield from $example(Rule::BRACKET_ROUND_OPEN, 'c', 'c');
        yield from $example(Rule::BRACKET_ROUND_OPEN, '🌈', '🌈');
        yield from $example(Rule::BRACKET_ROUND_CLOSE, 'd', 'd');
        yield from $example(Rule::BRACKET_ROUND_CLOSE, '⚓', '⚓');
        yield from $example(Rule::BRACKET_SQUARE_OPEN, 'e', 'e');
        yield from $example(Rule::BRACKET_SQUARE_OPEN, '☘', '☘');
        yield from $example(Rule::BRACKET_SQUARE_CLOSE, 'f', 'f');
        yield from $example(Rule::BRACKET_SQUARE_CLOSE, '🎷', '🎷');
        yield from $example(Rule::BRACKET_ANGLE_OPEN, 'g', 'g');
        yield from $example(Rule::BRACKET_ANGLE_OPEN, '🐒', '🐒');
        yield from $example(Rule::BRACKET_ANGLE_CLOSE, 'h', 'h');
        yield from $example(Rule::BRACKET_ANGLE_CLOSE, '💡', '💡');

        yield from $example(Rule::SYMBOL_PERIOD, 'i', 'i');
        yield from $example(Rule::SYMBOL_PERIOD, '?.', '?');
        yield from $example(Rule::SYMBOL_COLON, '-', '-');
        yield from $example(Rule::SYMBOL_COLON, '➗', '➗');
        yield from $example(Rule::SYMBOL_QUESTIONMARK, '❓', '❓');
        yield from $example(Rule::SYMBOL_EXCLAMATIONMARK, '❗', '❗');
        yield from $example(Rule::SYMBOL_COMMA, '.', '.');
        yield from $example(Rule::SYMBOL_DASH, '➖', '➖');
        yield from $example(Rule::SYMBOL_EQUALS, '<=', '<');
        yield from $example(Rule::SYMBOL_SLASH_FORWARD, '\\', '\\');
        yield from $example(Rule::SYMBOL_PIPE, '🌵', '🌵');
        yield from $example(Rule::SYMBOL_BOOLEAN_AND, '§§', '§');
        yield from $example(Rule::SYMBOL_BOOLEAN_OR, '//', '/');
        yield from $example(Rule::SYMBOL_STRICT_EQUALS, '!==', '!');
        yield from $example(Rule::SYMBOL_NOT_EQUALS, '===', '=');
        yield from $example(Rule::SYMBOL_GREATER_THAN_OR_EQUAL, '=>', '=');
        yield from $example(Rule::SYMBOL_LESS_THAN_OR_EQUAL, '=<', '=');
        yield from $example(Rule::SYMBOL_ARROW_SINGLE, '=>', '=');
        yield from $example(Rule::SYMBOL_OPTCHAIN, '??', '??');
        yield from $example(Rule::SYMBOL_NULLISH_COALESCE, '?.', '?.');

        yield from $example(Rule::WORD, '!NotAValidWord', '!');
        yield from $example(Rule::WORD, '?N0t4V4l1dW0rd', '?');
        yield from $example(Rule::WORD, '...1245NotAValidWord', '.');

        yield from $example(Rule::TEXT, '<', '<');
        yield from $example(Rule::TEXT, '>', '>');
        yield from $example(Rule::TEXT, '{', '{');
        yield from $example(Rule::TEXT, '}', '}');

        yield from $example(Rule::SPACE, '{', '{');
        yield from $example(Rule::SPACE, '}', '}');
        yield from $example(Rule::SPACE, '💡', '💡');
        yield from $example(Rule::SPACE, 'Anything but space', 'A');

        yield from $example(Rule::END_OF_LINE, '{', '{');
        yield from $example(Rule::END_OF_LINE, '}', '}');
        yield from $example(Rule::END_OF_LINE, '💡', '💡');
        yield from $example(Rule::END_OF_LINE, 'Anything but \\n', 'A');
    }

    /**
     * @dataProvider failingSingleTokenExamples
     * @test
     * @param string $source
     * @param Rule $expectedRule
     * @param Range $affectedRangeInSource
     * @param string $actualTokenValue
     * @return void
     */
    public function throwsIfCharacterSequenceDoesNotMatchSingleRule(
        string $source,
        Rule $expectedRule,
        Range $affectedRangeInSource,
        string $actualTokenValue
    ): void {
        $this->assertThrowsLexerException(
            function () use ($source, $expectedRule) {
                $this->lexer = new Lexer($source);
                $this->lexer->read($expectedRule);
            },
            LexerException::becauseOfUnexpectedCharacterSequence(
                expectedRules: Rules::from($expectedRule),
                affectedRangeInSource: $affectedRangeInSource,
                actualCharacterSequence: $actualTokenValue
            )
        );
    }

    /**
     * @return iterable<mixed>
     */
    public static function failingMultipleTokensExamples(): iterable
    {
        yield ($source = "# This is a comment\nThis is not a comment") => [
            $source,
            $rules = Rules::from(Rule::COMMENT, Rule::END_OF_LINE),
            3,
            LexerException::becauseOfUnexpectedCharacterSequence(
                expectedRules: $rules,
                affectedRangeInSource: Range::from(
                    Position::from(1, 0),
                    Position::from(1, 0)
                ),
                actualCharacterSequence: 'T'
            )
        ];
    }

    /**
     * @dataProvider failingMultipleTokensExamples
     * @test
     * @param string $source
     * @param Rules $rules
     * @param integer $numberOfReadOperations
     * @param LexerException $expectedLexerException
     * @return void
     */
    public function throwsIfCharacterSequenceDoesNotMatchMultipleRules(
        string $source,
        Rules $rules,
        int $numberOfReadOperations,
        LexerException $expectedLexerException
    ): void {
        $this->assertThrowsLexerException(
            function () use ($source, $rules, $numberOfReadOperations) {
                $this->lexer = new Lexer($source);

                foreach(range(0, $numberOfReadOperations) as $i) {
                    $this->lexer->readOneOf($rules);
                }
            },
            $expectedLexerException
        );
    }

    /**
     * @test
     */
    public function throwsIfSourceEndsUnexpectedlyWhileReadingASingleRule(): void
    {
        $this->assertThrowsLexerException(
            function () {
                $this->lexer = new Lexer('');
                $this->lexer->read(Rule::KEYWORD_NULL);
            },
            LexerException::becauseOfUnexpectedEndOfSource(
                expectedRules: Rules::from(Rule::KEYWORD_NULL),
                affectedRangeInSource: Range::from(
                    Position::from(0, 0),
                    Position::from(0, 0)
                )
            )
        );

        $this->assertThrowsLexerException(
            function () {
                $lexer = new Lexer('null');
                $lexer->read(Rule::KEYWORD_NULL);
                $lexer->read(Rule::KEYWORD_NULL);
            },
            LexerException::becauseOfUnexpectedEndOfSource(
                expectedRules: Rules::from(Rule::KEYWORD_NULL),
                affectedRangeInSource: Range::from(
                    Position::from(0, 0),
                    Position::from(0, 4)
                )
            )
        );
    }

    /**
     * @return iterable<mixed>
     */
    public static function multipleRuleUnexpectedEndOfSourceExamples(): iterable
    {
        yield ($source = '') => [
            $source,
            $rules = Rules::from(
                Rule::KEYWORD_RETURN,
                Rule::KEYWORD_NULL,
                Rule::SPACE
            ),
            1,
            LexerException::becauseOfUnexpectedEndOfSource(
                expectedRules: $rules,
                affectedRangeInSource: Range::from(
                    Position::from(0, 0),
                    Position::from(0, 0)
                )
            )
        ];

        yield ($source = 'return') => [
            $source,
            $rules = Rules::from(
                Rule::KEYWORD_RETURN,
                Rule::KEYWORD_NULL,
                Rule::SPACE
            ),
            2,
            LexerException::becauseOfUnexpectedEndOfSource(
                expectedRules: $rules,
                affectedRangeInSource: Range::from(
                    Position::from(0, 6),
                    Position::from(0, 6)
                )
            )
        ];

        yield ($source = 'return ') => [
            $source,
            $rules = Rules::from(
                Rule::KEYWORD_RETURN,
                Rule::KEYWORD_NULL,
                Rule::SPACE
            ),
            3,
            LexerException::becauseOfUnexpectedEndOfSource(
                expectedRules: $rules,
                affectedRangeInSource: Range::from(
                    Position::from(0, 7),
                    Position::from(0, 7)
                )
            )
        ];
    }

    /**
     * @dataProvider multipleRuleUnexpectedEndOfSourceExamples
     * @test
     * @param string $source
     * @param Rules $rules
     * @param integer $numberOfReadOperations
     * @param LexerException $expectedLexerException
     * @return void
     */
    public function throwsIfSourceEndsUnexpectedlyWhileReadingMultipleRules(
        string $source,
        Rules $rules,
        int $numberOfReadOperations,
        LexerException $expectedLexerException
    ): void {
        $this->assertThrowsLexerException(
            function () use ($source, $rules, $numberOfReadOperations) {
                $this->lexer = new Lexer($source);

                foreach(range(0, $numberOfReadOperations) as $i) {
                    $this->lexer->readOneOf($rules);
                }
            },
            $expectedLexerException
        );
    }

    /**
     * @test
     */
    public function skipsSpace(): void
    {
        // Single
        $this->lexer = new Lexer('return   ' . "\t\n\t" . '   42');

        $this->lexer->read(Rule::KEYWORD_RETURN);
        $this->lexer->skipSpace();
        $this->lexer->read(Rule::INTEGER_DECIMAL);

        $this->assertLexerState(
            startPosition: Position::from(1, 4),
            endPosition: Position::from(1, 5),
            buffer: '42',
            isEnd: true
        );

        // Multiple
        $this->lexer = new Lexer('return   ' . "\t\n\t" . '   42');

        $this->lexer->readOneOf(Rules::from(Rule::KEYWORD_RETURN, Rule::INTEGER_DECIMAL));
        $this->lexer->skipSpace();
        $this->lexer->readOneOf(Rules::from(Rule::KEYWORD_RETURN, Rule::INTEGER_DECIMAL));

        $this->assertLexerState(
            startPosition: Position::from(1, 4),
            endPosition: Position::from(1, 5),
            buffer: '42',
            isEnd: true
        );
    }

    /**
     * @test
     */
    public function skipsSpaceAndComments(): void
    {
        $source = <<<EOF
        import

        # Comment
        # Comment

            export # Another comment on this line
            component
        EOF;

        // Single
        $this->lexer = new Lexer($source);

        $this->lexer->read(Rule::KEYWORD_IMPORT);
        $this->lexer->skipSpaceAndComments();
        $this->lexer->read(Rule::KEYWORD_EXPORT);
        $this->lexer->skipSpaceAndComments();
        $this->lexer->read(Rule::KEYWORD_COMPONENT);

        $this->assertLexerState(
            startPosition: Position::from(6, 4),
            endPosition: Position::from(6, 12),
            buffer: 'component',
            isEnd: true
        );

        // Multiple
        $this->lexer = new Lexer($source);
        $this->lexer->readOneOf(
            Rules::from(
                Rule::KEYWORD_IMPORT,
                Rule::KEYWORD_EXPORT,
                Rule::KEYWORD_COMPONENT
            )
        );
        $this->lexer->skipSpaceAndComments();
        $this->lexer->readOneOf(
            Rules::from(
                Rule::KEYWORD_IMPORT,
                Rule::KEYWORD_EXPORT,
                Rule::KEYWORD_COMPONENT
            )
        );
        $this->lexer->skipSpaceAndComments();
        $this->lexer->readOneOf(
            Rules::from(
                Rule::KEYWORD_IMPORT,
                Rule::KEYWORD_EXPORT,
                Rule::KEYWORD_COMPONENT
            )
        );

        $this->assertLexerState(
            startPosition: Position::from(6, 4),
            endPosition: Position::from(6, 12),
            buffer: 'component',
            isEnd: true
        );
    }
}
