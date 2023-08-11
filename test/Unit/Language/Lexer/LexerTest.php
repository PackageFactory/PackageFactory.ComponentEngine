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
use PackageFactory\ComponentEngine\Language\Lexer\Token\TokenType;
use PackageFactory\ComponentEngine\Language\Lexer\Token\TokenTypes;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PHPUnit\Framework\TestCase;

final class LexerTest extends TestCase
{
    private Lexer $lexer;

    protected function assertLexerState(
        Position $startPosition,
        Position $endPosition,
        TokenType $tokenTypeUnderCursor,
        string $buffer,
        bool $isEnd
    ): void {
        $this->assertEquals(
            $startPosition,
            $this->lexer->getStartPosition(),
            'Failed asserting that start position of lexer equals'
        );

        $this->assertEquals(
            $endPosition,
            $this->lexer->getEndPosition(),
            'Failed asserting that end position of lexer equals'
        );

        $this->assertEquals(
            $tokenTypeUnderCursor,
            $this->lexer->getTokenTypeUnderCursor(),
            'Failed asserting that token type under cursor of lexer equals'
        );

        $this->assertEquals(
            $buffer,
            $this->lexer->getBuffer(),
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
            [$source, TokenType::COMMENT];
        yield ($source = '# This is a comment') =>
            [$source, TokenType::COMMENT];
        yield ($source = '# 🌵🆚⌚️: Multi-byte characters are not a problem inside a comment.') =>
            [$source, TokenType::COMMENT];

        yield ($source = 'from') =>
            [$source, TokenType::KEYWORD_FROM];
        yield ($source = 'import') =>
            [$source, TokenType::KEYWORD_IMPORT];
        yield ($source = 'export') =>
            [$source, TokenType::KEYWORD_EXPORT];
        yield ($source = 'enum') =>
            [$source, TokenType::KEYWORD_ENUM];
        yield ($source = 'struct') =>
            [$source, TokenType::KEYWORD_STRUCT];
        yield ($source = 'component') =>
            [$source, TokenType::KEYWORD_COMPONENT];
        yield ($source = 'match') =>
            [$source, TokenType::KEYWORD_MATCH];
        yield ($source = 'default') =>
            [$source, TokenType::KEYWORD_DEFAULT];
        yield ($source = 'return') =>
            [$source, TokenType::KEYWORD_RETURN];
        yield ($source = 'true') =>
            [$source, TokenType::KEYWORD_TRUE];
        yield ($source = 'false') =>
            [$source, TokenType::KEYWORD_FALSE];
        yield ($source = 'null') =>
            [$source, TokenType::KEYWORD_NULL];

        yield ($source = '"') =>
            [$source, TokenType::STRING_LITERAL_DELIMITER];
        yield ($source = 'Some string without any escapes') =>
            [$source, TokenType::STRING_LITERAL_CONTENT];
        yield ($source = '🌵🆚⌚️: Multi-byte characters are not a problem inside a string.') =>
            [$source, TokenType::STRING_LITERAL_CONTENT];

        yield ($source = '0b1001') =>
            [$source, TokenType::INTEGER_BINARY];
        yield ($source = '0o12345670') =>
            [$source, TokenType::INTEGER_OCTAL];
        yield ($source = '1234567890') =>
            [$source, TokenType::INTEGER_DECIMAL];
        yield ($source = '0xABCDEF1234567890') =>
            [$source, TokenType::INTEGER_HEXADECIMAL];

        yield ($source = '"""') =>
            [$source, TokenType::TEMPLATE_LITERAL_DELIMITER];
        yield ($source = 'Some string without escapes') =>
            [$source, TokenType::TEMPLATE_LITERAL_CONTENT];
        yield ($source = '🌵🆚⌚️: Multi-byte characters are not a problem inside of template literals.') =>
            [$source, TokenType::TEMPLATE_LITERAL_CONTENT];

        yield ($source = '\\\\') =>
            [$source, TokenType::ESCAPE_SEQUENCE_SINGLE_CHARACTER];
        yield ($source = '\\n') =>
            [$source, TokenType::ESCAPE_SEQUENCE_SINGLE_CHARACTER];
        yield ($source = '\\t') =>
            [$source, TokenType::ESCAPE_SEQUENCE_SINGLE_CHARACTER];
        yield ($source = '\\xA9') =>
            [$source, TokenType::ESCAPE_SEQUENCE_HEXADECIMAL];
        yield ($source = '\\u00A9') =>
            [$source, TokenType::ESCAPE_SEQUENCE_UNICODE];
        yield ($source = '\\u{2F804}') =>
            [$source, TokenType::ESCAPE_SEQUENCE_UNICODE_CODEPOINT];

        yield ($source = '{') =>
            [$source, TokenType::BRACKET_CURLY_OPEN];
        yield ($source = '}') =>
            [$source, TokenType::BRACKET_CURLY_CLOSE];
        yield ($source = '(') =>
            [$source, TokenType::BRACKET_ROUND_OPEN];
        yield ($source = ')') =>
            [$source, TokenType::BRACKET_ROUND_CLOSE];
        yield ($source = '[') =>
            [$source, TokenType::BRACKET_SQUARE_OPEN];
        yield ($source = ']') =>
            [$source, TokenType::BRACKET_SQUARE_CLOSE];
        yield ($source = '<') =>
            [$source, TokenType::BRACKET_ANGLE_OPEN];
        yield ($source = '>') =>
            [$source, TokenType::BRACKET_ANGLE_CLOSE];

        yield ($source = '.') =>
            [$source, TokenType::SYMBOL_PERIOD];
        yield ($source = ':') =>
            [$source, TokenType::SYMBOL_COLON];
        yield ($source = '?') =>
            [$source, TokenType::SYMBOL_QUESTIONMARK];
        yield ($source = '!') =>
            [$source, TokenType::SYMBOL_EXCLAMATIONMARK];
        yield ($source = ',') =>
            [$source, TokenType::SYMBOL_COMMA];
        yield ($source = '-') =>
            [$source, TokenType::SYMBOL_DASH];
        yield ($source = '=') =>
            [$source, TokenType::SYMBOL_EQUALS];
        yield ($source = '/') =>
            [$source, TokenType::SYMBOL_SLASH_FORWARD];
        yield ($source = '|') =>
            [$source, TokenType::SYMBOL_PIPE];
        yield ($source = '&&') =>
            [$source, TokenType::SYMBOL_BOOLEAN_AND];
        yield ($source = '||') =>
            [$source, TokenType::SYMBOL_BOOLEAN_OR];
        yield ($source = '===') =>
            [$source, TokenType::SYMBOL_STRICT_EQUALS];
        yield ($source = '!==') =>
            [$source, TokenType::SYMBOL_NOT_EQUALS];
        yield ($source = '>=') =>
            [$source, TokenType::SYMBOL_GREATER_THAN_OR_EQUAL];
        yield ($source = '<=') =>
            [$source, TokenType::SYMBOL_LESS_THAN_OR_EQUAL];
        yield ($source = '->') =>
            [$source, TokenType::SYMBOL_ARROW_SINGLE];
        yield ($source = '?.') =>
            [$source, TokenType::SYMBOL_OPTCHAIN];
        yield ($source = '??') =>
            [$source, TokenType::SYMBOL_NULLISH_COALESCE];

        yield ($source = 'ValidWord') =>
            [$source, TokenType::WORD];
        yield ($source = 'V4l1dW0rd') =>
            [$source, TokenType::WORD];
        yield ($source = '1245ValidWord') =>
            [$source, TokenType::WORD];

        yield ($source = 'JustSomeText.TextTerminates-Only-At??Space//Characters.') =>
            [$source, TokenType::TEXT];
        yield ($source = '🌵🆚⌚️') =>
            [$source, TokenType::TEXT];

        yield ($source = ' ') =>
            [$source, TokenType::SPACE];
        yield ($source = '    ') =>
            [$source, TokenType::SPACE];
        yield ($source = "\t") =>
            [$source, TokenType::SPACE];
        yield ($source = "\t\t\t\t") =>
            [$source, TokenType::SPACE];
        yield ($source = " \t \t \t \t ") =>
            [$source, TokenType::SPACE];

        yield ($source = "\n") =>
            [$source, TokenType::END_OF_LINE];
    }

    /**
     * @dataProvider singleTokenExamples
     * @test
     * @param string $source
     * @param TokenType $expectedTokenType
     * @return void
     */
    public function readSavesTokenOfGivenTypeIfMatchIsFound(string $source, TokenType $expectedTokenType): void
    {
        $this->lexer = new Lexer($source);
        $this->lexer->read($expectedTokenType);

        $this->assertLexerState(
            startPosition: Position::from(0, 0),
            endPosition: Position::from(0, \mb_strlen($source) - 1),
            tokenTypeUnderCursor: $expectedTokenType,
            buffer: $source,
            isEnd: true
        );
    }

    /**
     * @dataProvider singleTokenExamples
     * @test
     * @param string $source
     * @param TokenType $expectedTokenType
     * @return void
     */
    public function readOneOfSavesTokenOfGivenTypeIfMatchIsFound(string $source, TokenType $expectedTokenType): void
    {
        $this->lexer = new Lexer($source);
        $this->lexer->readOneOf(TokenTypes::from($expectedTokenType));

        $this->assertLexerState(
            startPosition: Position::from(0, 0),
            endPosition: Position::from(0, \mb_strlen($source) - 1),
            tokenTypeUnderCursor: $expectedTokenType,
            buffer: $source,
            isEnd: true
        );
    }

    /**
     * @return iterable<string,array{string,TokenTypes,array{array{int,int},array{int,int},TokenType,string}}>
     */
    public static function multipleTokensExamples(): iterable
    {
        yield ($source = "# This is a comment\n# This is also a comment") => [
            $source,
            TokenTypes::from(TokenType::COMMENT, TokenType::END_OF_LINE),
            [[0,  0], [0, 18], TokenType::COMMENT, '# This is a comment'],
            [[0, 19], [0, 19], TokenType::END_OF_LINE, "\n"],
            [[1,  0], [1, 23], TokenType::COMMENT, '# This is also a comment'],
        ];

        yield ($source = "1765224, -0xAB89CD, true\nnull") => [
            $source,
            TokenTypes::from(
                TokenType::SYMBOL_DASH,
                TokenType::SYMBOL_COMMA,
                TokenType::INTEGER_HEXADECIMAL,
                TokenType::INTEGER_DECIMAL,
                TokenType::SPACE,
                TokenType::END_OF_LINE,
                TokenType::KEYWORD_TRUE,
                TokenType::KEYWORD_NULL
            ),
            [[0,  0], [0,  6], TokenType::INTEGER_DECIMAL, '1765224'],
            [[0,  7], [0,  7], TokenType::SYMBOL_COMMA, ','],
            [[0,  8], [0,  8], TokenType::SPACE, ' '],
            [[0,  9], [0,  9], TokenType::SYMBOL_DASH, '-'],
            [[0, 10], [0, 17], TokenType::INTEGER_HEXADECIMAL, '0xAB89CD'],
            [[0, 18], [0, 18], TokenType::SYMBOL_COMMA, ','],
            [[0, 19], [0, 19], TokenType::SPACE, ' '],
            [[0, 20], [0, 23], TokenType::KEYWORD_TRUE, 'true'],
            [[0, 24], [0, 24], TokenType::END_OF_LINE, "\n"],
            [[1,  0], [1,  3], TokenType::KEYWORD_NULL, 'null'],
        ];

        yield ($source = '0b100101 892837 0xFFAAEE 0o75374') => [
            $source,
            TokenTypes::from(
                TokenType::INTEGER_BINARY,
                TokenType::INTEGER_OCTAL,
                TokenType::INTEGER_HEXADECIMAL,
                TokenType::INTEGER_DECIMAL,
                TokenType::SPACE
            ),
            [[0,  0], [0,  7], TokenType::INTEGER_BINARY, '0b100101'],
            [[0,  8], [0,  8], TokenType::SPACE, ' '],
            [[0,  9], [0, 14], TokenType::INTEGER_DECIMAL, '892837'],
            [[0, 15], [0, 15], TokenType::SPACE, ' '],
            [[0, 16], [0, 23], TokenType::INTEGER_HEXADECIMAL, '0xFFAAEE'],
            [[0, 24], [0, 24], TokenType::SPACE, ' '],
            [[0, 25], [0, 31], TokenType::INTEGER_OCTAL, '0o75374'],
        ];

        yield ($source = '"This is a string literal with \\n escapes \\xB1 \\u5FA9 \\u{1343E}!"') => [
            $source,
            TokenTypes::from(
                TokenType::STRING_LITERAL_DELIMITER,
                TokenType::STRING_LITERAL_CONTENT,
                TokenType::ESCAPE_SEQUENCE_SINGLE_CHARACTER,
                TokenType::ESCAPE_SEQUENCE_HEXADECIMAL,
                TokenType::ESCAPE_SEQUENCE_UNICODE,
                TokenType::ESCAPE_SEQUENCE_UNICODE_CODEPOINT
            ),
            [[0,  0], [0,  0], TokenType::STRING_LITERAL_DELIMITER, '"'],
            [[0,  1], [0, 30], TokenType::STRING_LITERAL_CONTENT, 'This is a string literal with '],
            [[0, 31], [0, 32], TokenType::ESCAPE_SEQUENCE_SINGLE_CHARACTER, '\\n'],
            [[0, 33], [0, 41], TokenType::STRING_LITERAL_CONTENT, ' escapes '],
            [[0, 42], [0, 45], TokenType::ESCAPE_SEQUENCE_HEXADECIMAL, '\\xB1'],
            [[0, 46], [0, 46], TokenType::STRING_LITERAL_CONTENT, ' '],
            [[0, 47], [0, 52], TokenType::ESCAPE_SEQUENCE_UNICODE, '\\u5FA9'],
            [[0, 53], [0, 53], TokenType::STRING_LITERAL_CONTENT, ' '],
            [[0, 54], [0, 62], TokenType::ESCAPE_SEQUENCE_UNICODE_CODEPOINT, '\\u{1343E}'],
            [[0, 63], [0, 63], TokenType::STRING_LITERAL_CONTENT, '!'],
            [[0, 64], [0, 64], TokenType::STRING_LITERAL_DELIMITER, '"']
        ];

        $source = <<<AFX
        """
            This is "template literal" content with \\n escapes \\xB1 \\u5FA9 \\u{1343E}
            and embedded expressions: {} :)
            """
        AFX;
        yield $source => [
            $source,
            TokenTypes::from(
                TokenType::TEMPLATE_LITERAL_DELIMITER,
                TokenType::SPACE,
                TokenType::TEMPLATE_LITERAL_CONTENT,
                TokenType::ESCAPE_SEQUENCE_SINGLE_CHARACTER,
                TokenType::ESCAPE_SEQUENCE_HEXADECIMAL,
                TokenType::ESCAPE_SEQUENCE_UNICODE,
                TokenType::ESCAPE_SEQUENCE_UNICODE_CODEPOINT,
                TokenType::END_OF_LINE,
                TokenType::BRACKET_CURLY_OPEN,
                TokenType::BRACKET_CURLY_CLOSE
            ),
            [[0,  0], [0,  2], TokenType::TEMPLATE_LITERAL_DELIMITER, '"""'],
            [[0,  3], [0,  3], TokenType::END_OF_LINE, "\n"],
            [[1,  0], [1,  3], TokenType::SPACE, '    '],
            [[1,  4], [1, 43], TokenType::TEMPLATE_LITERAL_CONTENT, 'This is "template literal" content with '],
            [[1, 44], [1, 45], TokenType::ESCAPE_SEQUENCE_SINGLE_CHARACTER, '\\n'],
            [[1, 46], [1, 46], TokenType::SPACE, ' '],
            [[1, 47], [1, 54], TokenType::TEMPLATE_LITERAL_CONTENT, 'escapes '],
            [[1, 55], [1, 58], TokenType::ESCAPE_SEQUENCE_HEXADECIMAL, '\\xB1'],
            [[1, 59], [1, 59], TokenType::SPACE, ' '],
            [[1, 60], [1, 65], TokenType::ESCAPE_SEQUENCE_UNICODE, '\\u5FA9'],
            [[1, 66], [1, 66], TokenType::SPACE, ' '],
            [[1, 67], [1, 75], TokenType::ESCAPE_SEQUENCE_UNICODE_CODEPOINT, '\\u{1343E}'],
            [[1, 76], [1, 76], TokenType::END_OF_LINE, "\n"],
            [[2,  0], [2,  3], TokenType::SPACE, '    '],
            [[2,  4], [2, 29], TokenType::TEMPLATE_LITERAL_CONTENT, 'and embedded expressions: '],
            [[2, 30], [2, 30], TokenType::BRACKET_CURLY_OPEN, '{'],
            [[2, 31], [2, 31], TokenType::BRACKET_CURLY_CLOSE, '}'],
            [[2, 32], [2, 32], TokenType::SPACE, ' '],
            [[2, 33], [2, 34], TokenType::TEMPLATE_LITERAL_CONTENT, ':)'],
            [[2, 35], [2, 35], TokenType::END_OF_LINE, "\n"],
            [[3,  0], [3,  3], TokenType::SPACE, '    '],
            [[3,  4], [3,  6], TokenType::TEMPLATE_LITERAL_DELIMITER, '"""'],
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
            TokenTypes::from(
                TokenType::BRACKET_ANGLE_OPEN,
                TokenType::WORD,
                TokenType::SPACE,
                TokenType::SYMBOL_EQUALS,
                TokenType::STRING_LITERAL_DELIMITER,
                TokenType::BRACKET_ANGLE_CLOSE,
                TokenType::END_OF_LINE,
                TokenType::SYMBOL_DASH,
                TokenType::SYMBOL_SLASH_FORWARD,
                TokenType::BRACKET_CURLY_OPEN,
                TokenType::BRACKET_CURLY_CLOSE,
                TokenType::SYMBOL_COLON
            ),
            [[0,  0], [0,  0], TokenType::BRACKET_ANGLE_OPEN, '<'],
            [[0,  1], [0,  1], TokenType::WORD, 'a'],
            [[0,  2], [0,  2], TokenType::SPACE, ' '],
            [[0,  3], [0,  6], TokenType::WORD, 'href'],
            [[0,  7], [0,  7], TokenType::SYMBOL_EQUALS, '='],
            [[0,  8], [0,  8], TokenType::STRING_LITERAL_DELIMITER, '"'],
            [[0,  9], [0,  9], TokenType::STRING_LITERAL_DELIMITER, '"'],
            [[0, 10], [0, 10], TokenType::BRACKET_ANGLE_CLOSE, '>'],
            [[0, 11], [0, 11], TokenType::END_OF_LINE, "\n"],
            [[1,  0], [1,  3], TokenType::SPACE, '    '],
            [[1,  4], [1,  4], TokenType::BRACKET_ANGLE_OPEN, '<'],
            [[1,  5], [1,  6], TokenType::WORD, 'my'],
            [[1,  7], [1,  7], TokenType::SYMBOL_DASH, '-'],
            [[1,  8], [1, 14], TokenType::WORD, 'element'],
            [[1, 15], [1, 15], TokenType::SYMBOL_SLASH_FORWARD, '/'],
            [[1, 16], [1, 16], TokenType::BRACKET_ANGLE_CLOSE, '>'],
            [[1, 17], [1, 17], TokenType::END_OF_LINE, "\n"],
            [[2,  0], [2,  3], TokenType::SPACE, '    '],
            [[2,  4], [2,  4], TokenType::BRACKET_ANGLE_OPEN, '<'],
            [[2,  5], [2,  7], TokenType::WORD, 'div'],
            [[2,  8], [2,  8], TokenType::SPACE, ' '],
            [[2,  9], [2, 13], TokenType::WORD, 'class'],
            [[2, 14], [2, 14], TokenType::SYMBOL_EQUALS, '='],
            [[2, 15], [2, 15], TokenType::BRACKET_CURLY_OPEN, '{'],
            [[2, 16], [2, 16], TokenType::BRACKET_CURLY_CLOSE, '}'],
            [[2, 17], [2, 17], TokenType::SPACE, ' '],
            [[2, 18], [2, 20], TokenType::WORD, 'foo'],
            [[2, 21], [2, 21], TokenType::SYMBOL_COLON, ':'],
            [[2, 22], [2, 24], TokenType::WORD, 'bar'],
            [[2, 25], [2, 25], TokenType::BRACKET_ANGLE_CLOSE, '>'],
            [[2, 26], [2, 26], TokenType::END_OF_LINE, "\n"],
            [[3,  0], [3,  3], TokenType::SPACE, '    '],
            [[3,  4], [3,  4], TokenType::BRACKET_ANGLE_OPEN, '<'],
            [[3,  5], [3,  5], TokenType::SYMBOL_SLASH_FORWARD, '/'],
            [[3,  6], [3,  8], TokenType::WORD, 'div'],
            [[3,  9], [3,  9], TokenType::BRACKET_ANGLE_CLOSE, '>'],
            [[3, 10], [3, 10], TokenType::END_OF_LINE, "\n"],
            [[4,  0], [4,  0], TokenType::BRACKET_ANGLE_OPEN, '<'],
            [[4,  1], [4,  1], TokenType::SYMBOL_SLASH_FORWARD, '/'],
            [[4,  2], [4,  2], TokenType::WORD, 'a'],
            [[4,  3], [4,  3], TokenType::BRACKET_ANGLE_CLOSE, '>'],
        ];

        $source = <<<AFX
        ThisIsSomeText-with-expressions{}
        line-breaks,   spaces   andTags<>inside.
        AFX;
        yield $source => [
            $source,
            TokenTypes::from(
                TokenType::TEXT,
                TokenType::BRACKET_CURLY_OPEN,
                TokenType::BRACKET_CURLY_CLOSE,
                TokenType::SPACE,
                TokenType::END_OF_LINE,
                TokenType::BRACKET_ANGLE_OPEN,
                TokenType::BRACKET_ANGLE_CLOSE
            ),
            [[0,  0], [0, 30], TokenType::TEXT, 'ThisIsSomeText-with-expressions'],
            [[0, 31], [0, 31], TokenType::BRACKET_CURLY_OPEN, '{'],
            [[0, 32], [0, 32], TokenType::BRACKET_CURLY_CLOSE, '}'],
            [[0, 33], [0, 33], TokenType::END_OF_LINE, "\n"],
            [[1,  0], [1, 11], TokenType::TEXT, 'line-breaks,'],
            [[1, 12], [1, 14], TokenType::SPACE, '   '],
            [[1, 15], [1, 20], TokenType::TEXT, 'spaces'],
            [[1, 21], [1, 23], TokenType::SPACE, '   '],
            [[1, 24], [1, 30], TokenType::TEXT, 'andTags'],
            [[1, 31], [1, 31], TokenType::BRACKET_ANGLE_OPEN, '<'],
            [[1, 32], [1, 32], TokenType::BRACKET_ANGLE_CLOSE, '>'],
            [[1, 33], [1, 39], TokenType::TEXT, 'inside.'],
        ];
    }

    /**
     * @dataProvider multipleTokensExamples
     * @test
     * @param string $source
     * @param array{array{int,int},array{int,int},TokenType,string} ...$expectedLexerStates
     * @return void
     */
    public function testReadOneOfWithMultipleTokenTypes(
        string $source,
        TokenTypes $tokenTypes,
        array ...$expectedLexerStates
    ): void {
        $this->lexer = new Lexer($source);

        foreach ($expectedLexerStates as $i => $expectedLexerState) {
            $this->lexer->readOneOf($tokenTypes);

            $this->assertLexerState(
                startPosition: Position::from(...$expectedLexerState[0]),
                endPosition: Position::from(...$expectedLexerState[1]),
                tokenTypeUnderCursor: $expectedLexerState[2],
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
            TokenType $type,
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

        yield from $example(TokenType::COMMENT, 'Anything that does not start with "#"', 'A');

        yield from $example(TokenType::KEYWORD_FROM, 'false', 'fa');
        yield from $example(TokenType::KEYWORD_IMPORT, 'implausible', 'impl');
        yield from $example(TokenType::KEYWORD_EXPORT, 'ex-machina', 'ex-');
        yield from $example(TokenType::KEYWORD_ENUM, 'enough', 'eno');
        yield from $example(TokenType::KEYWORD_STRUCT, 'strict', 'stri');
        yield from $example(TokenType::KEYWORD_COMPONENT, 'composition', 'compos');
        yield from $example(TokenType::KEYWORD_MATCH, 'matter', 'matt');
        yield from $example(TokenType::KEYWORD_DEFAULT, 'definition', 'defi');
        yield from $example(TokenType::KEYWORD_RETURN, 'retroactive', 'retr');
        yield from $example(TokenType::KEYWORD_TRUE, 'truth', 'trut');
        yield from $example(TokenType::KEYWORD_FALSE, 'falsify', 'falsi');
        yield from $example(TokenType::KEYWORD_NULL, 'nuclear', 'nuc');

        yield from $example(TokenType::STRING_LITERAL_DELIMITER, '\'', '\'');
        yield from $example(TokenType::STRING_LITERAL_CONTENT, '"', '"');
        yield from $example(TokenType::STRING_LITERAL_CONTENT, '\\', '\\');

        yield from $example(TokenType::INTEGER_BINARY, '001001', '00');
        yield from $example(TokenType::INTEGER_BINARY, '0b21', '0b2');
        yield from $example(TokenType::INTEGER_OCTAL, '0p12345670', '0p');
        yield from $example(TokenType::INTEGER_OCTAL, '0o84', '0o8');
        yield from $example(TokenType::INTEGER_DECIMAL, ' ', ' ');
        yield from $example(TokenType::INTEGER_DECIMAL, 'foo', 'f');
        yield from $example(TokenType::INTEGER_HEXADECIMAL, '0xG', '0xG');
        yield from $example(TokenType::INTEGER_HEXADECIMAL, '0yFFAA00', '0y');

        yield from $example(TokenType::TEMPLATE_LITERAL_DELIMITER, '`', '`');
        yield from $example(TokenType::TEMPLATE_LITERAL_CONTENT, '{', '{');
        yield from $example(TokenType::TEMPLATE_LITERAL_CONTENT, '}', '}');
        yield from $example(TokenType::TEMPLATE_LITERAL_CONTENT, "\n", "\n");
        yield from $example(TokenType::TEMPLATE_LITERAL_CONTENT, '\\', '\\');

        yield from $example(TokenType::ESCAPE_SEQUENCE_SINGLE_CHARACTER, '\\o', '\\o');
        yield from $example(TokenType::ESCAPE_SEQUENCE_SINGLE_CHARACTER, '\\ü', '\\ü');
        yield from $example(TokenType::ESCAPE_SEQUENCE_SINGLE_CHARACTER, '\\£', '\\£');
        yield from $example(TokenType::ESCAPE_SEQUENCE_SINGLE_CHARACTER, '\\И', '\\И');
        yield from $example(TokenType::ESCAPE_SEQUENCE_SINGLE_CHARACTER, '\\ह', '\\ह');
        yield from $example(TokenType::ESCAPE_SEQUENCE_SINGLE_CHARACTER, '\\€', '\\€');
        yield from $example(TokenType::ESCAPE_SEQUENCE_SINGLE_CHARACTER, '\\𐍈', '\\𐍈');
        yield from $example(TokenType::ESCAPE_SEQUENCE_SINGLE_CHARACTER, '\\8', '\\8');
        yield from $example(TokenType::ESCAPE_SEQUENCE_HEXADECIMAL, '\\x9G', '\\x9G');
        yield from $example(TokenType::ESCAPE_SEQUENCE_UNICODE, '\\u00AY', '\\u00AY');
        yield from $example(TokenType::ESCAPE_SEQUENCE_UNICODE_CODEPOINT, '\\u{2F8O4}', '\\u{2F8O');

        yield from $example(TokenType::BRACKET_CURLY_OPEN, 'a', 'a');
        yield from $example(TokenType::BRACKET_CURLY_OPEN, '😱', '😱');
        yield from $example(TokenType::BRACKET_CURLY_CLOSE, 'b', 'b');
        yield from $example(TokenType::BRACKET_CURLY_CLOSE, '🖖', '🖖');
        yield from $example(TokenType::BRACKET_ROUND_OPEN, 'c', 'c');
        yield from $example(TokenType::BRACKET_ROUND_OPEN, '🌈', '🌈');
        yield from $example(TokenType::BRACKET_ROUND_CLOSE, 'd', 'd');
        yield from $example(TokenType::BRACKET_ROUND_CLOSE, '⚓', '⚓');
        yield from $example(TokenType::BRACKET_SQUARE_OPEN, 'e', 'e');
        yield from $example(TokenType::BRACKET_SQUARE_OPEN, '☘', '☘');
        yield from $example(TokenType::BRACKET_SQUARE_CLOSE, 'f', 'f');
        yield from $example(TokenType::BRACKET_SQUARE_CLOSE, '🎷', '🎷');
        yield from $example(TokenType::BRACKET_ANGLE_OPEN, 'g', 'g');
        yield from $example(TokenType::BRACKET_ANGLE_OPEN, '🐒', '🐒');
        yield from $example(TokenType::BRACKET_ANGLE_CLOSE, 'h', 'h');
        yield from $example(TokenType::BRACKET_ANGLE_CLOSE, '💡', '💡');

        yield from $example(TokenType::SYMBOL_PERIOD, 'i', 'i');
        yield from $example(TokenType::SYMBOL_PERIOD, '?.', '?');
        yield from $example(TokenType::SYMBOL_COLON, '-', '-');
        yield from $example(TokenType::SYMBOL_COLON, '➗', '➗');
        yield from $example(TokenType::SYMBOL_QUESTIONMARK, '❓', '❓');
        yield from $example(TokenType::SYMBOL_EXCLAMATIONMARK, '❗', '❗');
        yield from $example(TokenType::SYMBOL_COMMA, '.', '.');
        yield from $example(TokenType::SYMBOL_DASH, '➖', '➖');
        yield from $example(TokenType::SYMBOL_EQUALS, '<=', '<');
        yield from $example(TokenType::SYMBOL_SLASH_FORWARD, '\\', '\\');
        yield from $example(TokenType::SYMBOL_PIPE, '🌵', '🌵');
        yield from $example(TokenType::SYMBOL_BOOLEAN_AND, '§§', '§');
        yield from $example(TokenType::SYMBOL_BOOLEAN_OR, '//', '/');
        yield from $example(TokenType::SYMBOL_STRICT_EQUALS, '!==', '!');
        yield from $example(TokenType::SYMBOL_NOT_EQUALS, '===', '=');
        yield from $example(TokenType::SYMBOL_GREATER_THAN_OR_EQUAL, '=>', '=');
        yield from $example(TokenType::SYMBOL_LESS_THAN_OR_EQUAL, '=<', '=');
        yield from $example(TokenType::SYMBOL_ARROW_SINGLE, '=>', '=');
        yield from $example(TokenType::SYMBOL_OPTCHAIN, '??', '??');
        yield from $example(TokenType::SYMBOL_NULLISH_COALESCE, '?.', '?.');

        yield from $example(TokenType::WORD, '!NotAValidWord', '!');
        yield from $example(TokenType::WORD, '?N0t4V4l1dW0rd', '?');
        yield from $example(TokenType::WORD, '...1245NotAValidWord', '.');

        yield from $example(TokenType::TEXT, '<', '<');
        yield from $example(TokenType::TEXT, '>', '>');
        yield from $example(TokenType::TEXT, '{', '{');
        yield from $example(TokenType::TEXT, '}', '}');

        yield from $example(TokenType::SPACE, '{', '{');
        yield from $example(TokenType::SPACE, '}', '}');
        yield from $example(TokenType::SPACE, '💡', '💡');
        yield from $example(TokenType::SPACE, 'Anything but space', 'A');

        yield from $example(TokenType::END_OF_LINE, '{', '{');
        yield from $example(TokenType::END_OF_LINE, '}', '}');
        yield from $example(TokenType::END_OF_LINE, '💡', '💡');
        yield from $example(TokenType::END_OF_LINE, 'Anything but \\n', 'A');
    }

    /**
     * @dataProvider failingSingleTokenExamples
     * @test
     * @param string $source
     * @param TokenType $expectedTokenType
     * @param Range $affectedRangeInSource
     * @param string $actualTokenValue
     * @return void
     */
    public function throwsIfCharacterSequenceDoesNotMatchSingleTokenType(
        string $source,
        TokenType $expectedTokenType,
        Range $affectedRangeInSource,
        string $actualTokenValue
    ): void {
        $this->assertThrowsLexerException(
            function () use ($source, $expectedTokenType) {
                $this->lexer = new Lexer($source);
                $this->lexer->read($expectedTokenType);
            },
            LexerException::becauseOfUnexpectedCharacterSequence(
                expectedTokenTypes: TokenTypes::from($expectedTokenType),
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
            $tokenTypes = TokenTypes::from(TokenType::COMMENT, TokenType::END_OF_LINE),
            3,
            LexerException::becauseOfUnexpectedCharacterSequence(
                expectedTokenTypes: $tokenTypes,
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
     * @param TokenTypes $tokenTypes
     * @param integer $numberOfReadOperations
     * @param LexerException $expectedLexerException
     * @return void
     */
    public function throwsIfCharacterSequenceDoesNotMatchMultipleTokenTypes(
        string $source,
        TokenTypes $tokenTypes,
        int $numberOfReadOperations,
        LexerException $expectedLexerException
    ): void {
        $this->assertThrowsLexerException(
            function () use ($source, $tokenTypes, $numberOfReadOperations) {
                $this->lexer = new Lexer($source);

                foreach(range(0, $numberOfReadOperations) as $i) {
                    $this->lexer->readOneOf($tokenTypes);
                }
            },
            $expectedLexerException
        );
    }

    /**
     * @test
     */
    public function throwsIfSourceEndsUnexpectedlyWhileReadingASingleTokenType(): void
    {
        $this->assertThrowsLexerException(
            function () {
                $this->lexer = new Lexer('');
                $this->lexer->read(TokenType::KEYWORD_NULL);
            },
            LexerException::becauseOfUnexpectedEndOfSource(
                expectedTokenTypes: TokenTypes::from(TokenType::KEYWORD_NULL),
                affectedRangeInSource: Range::from(
                    Position::from(0, 0),
                    Position::from(0, 0)
                )
            )
        );

        $this->assertThrowsLexerException(
            function () {
                $lexer = new Lexer('null');
                $lexer->read(TokenType::KEYWORD_NULL);
                $lexer->read(TokenType::KEYWORD_NULL);
            },
            LexerException::becauseOfUnexpectedEndOfSource(
                expectedTokenTypes: TokenTypes::from(TokenType::KEYWORD_NULL),
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
    public static function multipleTokenTypeUnexpectedEndOfSourceExamples(): iterable
    {
        yield ($source = '') => [
            $source,
            $tokenTypes = TokenTypes::from(
                TokenType::KEYWORD_RETURN,
                TokenType::KEYWORD_NULL,
                TokenType::SPACE
            ),
            1,
            LexerException::becauseOfUnexpectedEndOfSource(
                expectedTokenTypes: $tokenTypes,
                affectedRangeInSource: Range::from(
                    Position::from(0, 0),
                    Position::from(0, 0)
                )
            )
        ];

        yield ($source = 'return') => [
            $source,
            $tokenTypes = TokenTypes::from(
                TokenType::KEYWORD_RETURN,
                TokenType::KEYWORD_NULL,
                TokenType::SPACE
            ),
            2,
            LexerException::becauseOfUnexpectedEndOfSource(
                expectedTokenTypes: $tokenTypes,
                affectedRangeInSource: Range::from(
                    Position::from(0, 6),
                    Position::from(0, 6)
                )
            )
        ];

        yield ($source = 'return ') => [
            $source,
            $tokenTypes = TokenTypes::from(
                TokenType::KEYWORD_RETURN,
                TokenType::KEYWORD_NULL,
                TokenType::SPACE
            ),
            3,
            LexerException::becauseOfUnexpectedEndOfSource(
                expectedTokenTypes: $tokenTypes,
                affectedRangeInSource: Range::from(
                    Position::from(0, 7),
                    Position::from(0, 7)
                )
            )
        ];
    }

    /**
     * @dataProvider multipleTokenTypeUnexpectedEndOfSourceExamples
     * @test
     * @param string $source
     * @param TokenTypes $tokenTypes
     * @param integer $numberOfReadOperations
     * @param LexerException $expectedLexerException
     * @return void
     */
    public function throwsIfSourceEndsUnexpectedlyWhileReadingMultipleTokenTypes(
        string $source,
        TokenTypes $tokenTypes,
        int $numberOfReadOperations,
        LexerException $expectedLexerException
    ): void {
        $this->assertThrowsLexerException(
            function () use ($source, $tokenTypes, $numberOfReadOperations) {
                $this->lexer = new Lexer($source);

                foreach(range(0, $numberOfReadOperations) as $i) {
                    $this->lexer->readOneOf($tokenTypes);
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

        $this->lexer->read(TokenType::KEYWORD_RETURN);
        $this->lexer->skipSpace();
        $this->lexer->read(TokenType::INTEGER_DECIMAL);

        $this->assertLexerState(
            startPosition: Position::from(1, 4),
            endPosition: Position::from(1, 5),
            tokenTypeUnderCursor: TokenType::INTEGER_DECIMAL,
            buffer: '42',
            isEnd: true
        );

        // Multiple
        $this->lexer = new Lexer('return   ' . "\t\n\t" . '   42');

        $this->lexer->readOneOf(TokenTypes::from(TokenType::KEYWORD_RETURN, TokenType::INTEGER_DECIMAL));
        $this->lexer->skipSpace();
        $this->lexer->readOneOf(TokenTypes::from(TokenType::KEYWORD_RETURN, TokenType::INTEGER_DECIMAL));

        $this->assertLexerState(
            startPosition: Position::from(1, 4),
            endPosition: Position::from(1, 5),
            tokenTypeUnderCursor: TokenType::INTEGER_DECIMAL,
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

        $this->lexer->read(TokenType::KEYWORD_IMPORT);
        $this->lexer->skipSpaceAndComments();
        $this->lexer->read(TokenType::KEYWORD_EXPORT);
        $this->lexer->skipSpaceAndComments();
        $this->lexer->read(TokenType::KEYWORD_COMPONENT);

        $this->assertLexerState(
            startPosition: Position::from(6, 4),
            endPosition: Position::from(6, 12),
            tokenTypeUnderCursor: TokenType::KEYWORD_COMPONENT,
            buffer: 'component',
            isEnd: true
        );

        // Multiple
        $this->lexer = new Lexer($source);
        $this->lexer->readOneOf(
            TokenTypes::from(
                TokenType::KEYWORD_IMPORT,
                TokenType::KEYWORD_EXPORT,
                TokenType::KEYWORD_COMPONENT
            )
        );
        $this->lexer->skipSpaceAndComments();
        $this->lexer->readOneOf(
            TokenTypes::from(
                TokenType::KEYWORD_IMPORT,
                TokenType::KEYWORD_EXPORT,
                TokenType::KEYWORD_COMPONENT
            )
        );
        $this->lexer->skipSpaceAndComments();
        $this->lexer->readOneOf(
            TokenTypes::from(
                TokenType::KEYWORD_IMPORT,
                TokenType::KEYWORD_EXPORT,
                TokenType::KEYWORD_COMPONENT
            )
        );

        $this->assertLexerState(
            startPosition: Position::from(6, 4),
            endPosition: Position::from(6, 12),
            tokenTypeUnderCursor: TokenType::KEYWORD_COMPONENT,
            buffer: 'component',
            isEnd: true
        );
    }
}
