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

use AssertionError;
use PackageFactory\ComponentEngine\Language\Lexer\Lexer;
use PackageFactory\ComponentEngine\Language\Lexer\LexerException;
use PackageFactory\ComponentEngine\Language\Lexer\Token\Token;
use PackageFactory\ComponentEngine\Language\Lexer\Token\TokenType;
use PackageFactory\ComponentEngine\Language\Lexer\Token\TokenTypes;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Parser\Source\Range;
use PHPUnit\Framework\TestCase;

final class LexerTest extends TestCase
{
    /**
     * @param array{int,int} $startAsArray
     * @param array{int,int} $endAsArray
     * @return Range
     */
    protected static function range(array $startAsArray, array $endAsArray): Range
    {
        return Range::from(
            new Position(...$startAsArray),
            new Position(...$endAsArray)
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
            [$source, TokenType::SYMBOL_STRICT_EQUALs];
        yield ($source = '!==') =>
            [$source, TokenType::SYMBOL_NOT_EQUALs];
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

        yield ($source = 'Just some text. Nothing special.') =>
            [$source, TokenType::TEXT];
        yield ($source = '🌵🆚⌚️: Multi-byte characters are not a problem inside of text.') =>
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
    public function readsSingleToken(string $source, TokenType $expectedTokenType): void
    {
        $lexer = new Lexer($source);
        $lexer->read($expectedTokenType);

        $this->assertEquals(
            new Token(
                rangeInSource: self::range([0, 0], [0, \mb_strlen($source) - 1]),
                type: $expectedTokenType,
                value: $source
            ),
            $lexer->getTokenUnderCursor()
        );
    }

    /**
     * @return iterable<mixed>
     */
    public static function multipleTokensExamples(): iterable
    {
        yield ($source = "# This is a comment\n# This is also a comment") => [
            $source,
            TokenTypes::from(TokenType::COMMENT, TokenType::END_OF_LINE),
            new Token(self::range([0, 0], [0, 18]), TokenType::COMMENT, '# This is a comment'),
            new Token(self::range([0, 19], [0, 19]), TokenType::END_OF_LINE, "\n"),
            new Token(self::range([1, 0], [1, 23]), TokenType::COMMENT, '# This is also a comment')
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
            new Token(self::range([0, 0], [0, 6]), TokenType::INTEGER_DECIMAL, '1765224'),
            new Token(self::range([0, 7], [0, 7]), TokenType::SYMBOL_COMMA, ','),
            new Token(self::range([0, 8], [0, 8]), TokenType::SPACE, ' '),
            new Token(self::range([0, 9], [0, 9]), TokenType::SYMBOL_DASH, '-'),
            new Token(self::range([0, 10], [0, 17]), TokenType::INTEGER_HEXADECIMAL, '0xAB89CD'),
            new Token(self::range([0, 18], [0, 18]), TokenType::SYMBOL_COMMA, ','),
            new Token(self::range([0, 19], [0, 19]), TokenType::SPACE, ' '),
            new Token(self::range([0, 20], [0, 23]), TokenType::KEYWORD_TRUE, 'true'),
            new Token(self::range([0, 24], [0, 24]), TokenType::END_OF_LINE, "\n"),
            new Token(self::range([1, 0], [1, 3]), TokenType::KEYWORD_NULL, 'null')
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
            new Token(self::range([0, 0], [0, 7]), TokenType::INTEGER_BINARY, '0b100101'),
            new Token(self::range([0, 8], [0, 8]), TokenType::SPACE, ' '),
            new Token(self::range([0, 9], [0, 14]), TokenType::INTEGER_DECIMAL, '892837'),
            new Token(self::range([0, 15], [0, 15]), TokenType::SPACE, ' '),
            new Token(self::range([0, 16], [0, 23]), TokenType::INTEGER_HEXADECIMAL, '0xFFAAEE'),
            new Token(self::range([0, 24], [0, 24]), TokenType::SPACE, ' '),
            new Token(self::range([0, 25], [0, 31]), TokenType::INTEGER_OCTAL, '0o75374'),
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
            new Token(self::range([0, 0], [0, 0]), TokenType::STRING_LITERAL_DELIMITER, '"'),
            new Token(self::range([0, 1], [0, 30]), TokenType::STRING_LITERAL_CONTENT, 'This is a string literal with '),
            new Token(self::range([0, 31], [0, 32]), TokenType::ESCAPE_SEQUENCE_SINGLE_CHARACTER, '\\n'),
            new Token(self::range([0, 33], [0, 41]), TokenType::STRING_LITERAL_CONTENT, ' escapes '),
            new Token(self::range([0, 42], [0, 45]), TokenType::ESCAPE_SEQUENCE_HEXADECIMAL, '\\xB1'),
            new Token(self::range([0, 46], [0, 46]), TokenType::STRING_LITERAL_CONTENT, ' '),
            new Token(self::range([0, 47], [0, 52]), TokenType::ESCAPE_SEQUENCE_UNICODE, '\\u5FA9'),
            new Token(self::range([0, 53], [0, 53]), TokenType::STRING_LITERAL_CONTENT, ' '),
            new Token(self::range([0, 54], [0, 62]), TokenType::ESCAPE_SEQUENCE_UNICODE_CODEPOINT, '\\u{1343E}'),
            new Token(self::range([0, 63], [0, 63]), TokenType::STRING_LITERAL_CONTENT, '!'),
            new Token(self::range([0, 64], [0, 64]), TokenType::STRING_LITERAL_DELIMITER, '"')
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
            new Token(self::range([0, 0], [0, 2]), TokenType::TEMPLATE_LITERAL_DELIMITER, '"""'),
            new Token(self::range([0, 3], [0, 3]), TokenType::END_OF_LINE, "\n"),
            new Token(self::range([1, 0], [1, 3]), TokenType::SPACE, '    '),
            new Token(self::range([1, 4], [1, 43]), TokenType::TEMPLATE_LITERAL_CONTENT, 'This is "template literal" content with '),
            new Token(self::range([1, 44], [1, 45]), TokenType::ESCAPE_SEQUENCE_SINGLE_CHARACTER, '\\n'),
            new Token(self::range([1, 46], [1, 46]), TokenType::SPACE, ' '),
            new Token(self::range([1, 47], [1, 54]), TokenType::TEMPLATE_LITERAL_CONTENT, 'escapes '),
            new Token(self::range([1, 55], [1, 58]), TokenType::ESCAPE_SEQUENCE_HEXADECIMAL, '\\xB1'),
            new Token(self::range([1, 59], [1, 59]), TokenType::SPACE, ' '),
            new Token(self::range([1, 60], [1, 65]), TokenType::ESCAPE_SEQUENCE_UNICODE, '\\u5FA9'),
            new Token(self::range([1, 66], [1, 66]), TokenType::SPACE, ' '),
            new Token(self::range([1, 67], [1, 75]), TokenType::ESCAPE_SEQUENCE_UNICODE_CODEPOINT, '\\u{1343E}'),
            new Token(self::range([1, 76], [1, 76]), TokenType::END_OF_LINE, "\n"),
            new Token(self::range([2, 0], [2, 3]), TokenType::SPACE, '    '),
            new Token(self::range([2, 4], [2, 29]), TokenType::TEMPLATE_LITERAL_CONTENT, 'and embedded expressions: '),
            new Token(self::range([2, 30], [2, 30]), TokenType::BRACKET_CURLY_OPEN, '{'),
            new Token(self::range([2, 31], [2, 31]), TokenType::BRACKET_CURLY_CLOSE, '}'),
            new Token(self::range([2, 32], [2, 32]), TokenType::SPACE, ' '),
            new Token(self::range([2, 33], [2, 34]), TokenType::TEMPLATE_LITERAL_CONTENT, ':)'),
            new Token(self::range([2, 35], [2, 35]), TokenType::END_OF_LINE, "\n"),
            new Token(self::range([3, 0], [3, 3]), TokenType::SPACE, '    '),
            new Token(self::range([3, 4], [3, 6]), TokenType::TEMPLATE_LITERAL_DELIMITER, '"""'),
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
            new Token(self::range([0, 0], [0, 0]), TokenType::BRACKET_ANGLE_OPEN, '<'),
            new Token(self::range([0, 1], [0, 1]), TokenType::WORD, 'a'),
            new Token(self::range([0, 2], [0, 2]), TokenType::SPACE, ' '),
            new Token(self::range([0, 3], [0, 6]), TokenType::WORD, 'href'),
            new Token(self::range([0, 7], [0, 7]), TokenType::SYMBOL_EQUALS, '='),
            new Token(self::range([0, 8], [0, 8]), TokenType::STRING_LITERAL_DELIMITER, '"'),
            new Token(self::range([0, 9], [0, 9]), TokenType::STRING_LITERAL_DELIMITER, '"'),
            new Token(self::range([0, 10], [0, 10]), TokenType::BRACKET_ANGLE_CLOSE, '>'),
            new Token(self::range([0, 11], [0, 11]), TokenType::END_OF_LINE, "\n"),
            new Token(self::range([1, 0], [1, 3]), TokenType::SPACE, '    '),
            new Token(self::range([1, 4], [1, 4]), TokenType::BRACKET_ANGLE_OPEN, '<'),
            new Token(self::range([1, 5], [1, 6]), TokenType::WORD, 'my'),
            new Token(self::range([1, 7], [1, 7]), TokenType::SYMBOL_DASH, '-'),
            new Token(self::range([1, 8], [1, 14]), TokenType::WORD, 'element'),
            new Token(self::range([1, 15], [1, 15]), TokenType::SYMBOL_SLASH_FORWARD, '/'),
            new Token(self::range([1, 16], [1, 16]), TokenType::BRACKET_ANGLE_CLOSE, '>'),
            new Token(self::range([1, 17], [1, 17]), TokenType::END_OF_LINE, "\n"),
            new Token(self::range([2, 0], [2, 3]), TokenType::SPACE, '    '),
            new Token(self::range([2, 4], [2, 4]), TokenType::BRACKET_ANGLE_OPEN, '<'),
            new Token(self::range([2, 5], [2, 7]), TokenType::WORD, 'div'),
            new Token(self::range([2, 8], [2, 8]), TokenType::SPACE, ' '),
            new Token(self::range([2, 9], [2, 13]), TokenType::WORD, 'class'),
            new Token(self::range([2, 14], [2, 14]), TokenType::SYMBOL_EQUALS, '='),
            new Token(self::range([2, 15], [2, 15]), TokenType::BRACKET_CURLY_OPEN, '{'),
            new Token(self::range([2, 16], [2, 16]), TokenType::BRACKET_CURLY_CLOSE, '}'),
            new Token(self::range([2, 17], [2, 17]), TokenType::SPACE, ' '),
            new Token(self::range([2, 18], [2, 20]), TokenType::WORD, 'foo'),
            new Token(self::range([2, 21], [2, 21]), TokenType::SYMBOL_COLON, ':'),
            new Token(self::range([2, 22], [2, 24]), TokenType::WORD, 'bar'),
            new Token(self::range([2, 25], [2, 25]), TokenType::BRACKET_ANGLE_CLOSE, '>'),
            new Token(self::range([2, 26], [2, 26]), TokenType::END_OF_LINE, "\n"),
            new Token(self::range([3, 0], [3, 3]), TokenType::SPACE, '    '),
            new Token(self::range([3, 4], [3, 4]), TokenType::BRACKET_ANGLE_OPEN, '<'),
            new Token(self::range([3, 5], [3, 5]), TokenType::SYMBOL_SLASH_FORWARD, '/'),
            new Token(self::range([3, 6], [3, 8]), TokenType::WORD, 'div'),
            new Token(self::range([3, 9], [3, 9]), TokenType::BRACKET_ANGLE_CLOSE, '>'),
            new Token(self::range([3, 10], [3, 10]), TokenType::END_OF_LINE, "\n"),
            new Token(self::range([4, 0], [4, 0]), TokenType::BRACKET_ANGLE_OPEN, '<'),
            new Token(self::range([4, 1], [4, 1]), TokenType::SYMBOL_SLASH_FORWARD, '/'),
            new Token(self::range([4, 2], [4, 2]), TokenType::WORD, 'a'),
            new Token(self::range([4, 3], [4, 3]), TokenType::BRACKET_ANGLE_CLOSE, '>'),
        ];

        $source = <<<AFX
        This is some text with expressions {}
        and tags <> inside.
        AFX;
        yield $source => [
            $source,
            TokenTypes::from(
                TokenType::TEXT,
                TokenType::BRACKET_CURLY_OPEN,
                TokenType::BRACKET_CURLY_CLOSE,
                TokenType::END_OF_LINE,
                TokenType::BRACKET_ANGLE_OPEN,
                TokenType::BRACKET_ANGLE_CLOSE
            ),
            new Token(self::range([0, 0], [0, 34]), TokenType::TEXT, 'This is some text with expressions '),
            new Token(self::range([0, 35], [0, 35]), TokenType::BRACKET_CURLY_OPEN, '{'),
            new Token(self::range([0, 36], [0, 36]), TokenType::BRACKET_CURLY_CLOSE, '}'),
            new Token(self::range([0, 37], [0, 37]), TokenType::END_OF_LINE, "\n"),
            new Token(self::range([1, 0], [1, 8]), TokenType::TEXT, 'and tags '),
            new Token(self::range([1, 9], [1, 9]), TokenType::BRACKET_ANGLE_OPEN, '<'),
            new Token(self::range([1, 10], [1, 10]), TokenType::BRACKET_ANGLE_CLOSE, '>'),
            new Token(self::range([1, 11], [1, 18]), TokenType::TEXT, ' inside.'),
        ];
    }

    /**
     * @dataProvider multipleTokensExamples
     * @test
     * @param string $source
     * @param Token ...$expectedTokens
     * @return void
     */
    public function readsMultipleTokens(
        string $source,
        TokenTypes $tokenTypes,
        Token ...$expectedTokens
    ): void {
        $lexer = new Lexer($source);

        $actualTokens = [];
        foreach ($expectedTokens as $token) {
            $lexer->readOneOf($tokenTypes);
            $actualTokens[] = $lexer->getTokenUnderCursor();
        }

        $this->assertEquals($expectedTokens, $actualTokens);
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
                self::range([0, 0], [0, \mb_strlen($unexpectedCharacterSequence) - 1]),
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
        yield from $example(TokenType::STRING_LITERAL_CONTENT, "\n", "\n");
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
        yield from $example(TokenType::SYMBOL_STRICT_EQUALs, '!==', '!');
        yield from $example(TokenType::SYMBOL_NOT_EQUALs, '===', '=');
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
                $lexer = new Lexer($source);
                $lexer->read($expectedTokenType);
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
                affectedRangeInSource: self::range([1, 0], [1, 0]),
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
                $lexer = new Lexer($source);

                foreach(range(0, $numberOfReadOperations) as $i) {
                    $lexer->readOneOf($tokenTypes);
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
                $lexer = new Lexer('');
                $lexer->read(TokenType::KEYWORD_NULL);
            },
            LexerException::becauseOfUnexpectedEndOfSource(
                expectedTokenTypes: TokenTypes::from(TokenType::KEYWORD_NULL),
                affectedRangeInSource: self::range([0, 0], [0, 0])
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
                affectedRangeInSource: self::range([0, 0], [0, 4])
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
                affectedRangeInSource: self::range([0, 0], [0, 0])
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
                affectedRangeInSource: self::range([0, 6], [0, 6])
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
                affectedRangeInSource: self::range([0, 7], [0, 7])
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
                $lexer = new Lexer($source);

                foreach(range(0, $numberOfReadOperations) as $i) {
                    $lexer->readOneOf($tokenTypes);
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
        $lexer = new Lexer('return   ' . "\t\n\t" . '   42');

        $lexer->read(TokenType::KEYWORD_RETURN);
        $lexer->skipSpace();
        $lexer->read(TokenType::INTEGER_DECIMAL);

        $this->assertEquals(
            new Token(
                rangeInSource: self::range([1, 4], [1, 5]),
                type: TokenType::INTEGER_DECIMAL,
                value: '42'
            ),
            $lexer->getTokenUnderCursor()
        );

        // Multiple
        $lexer = new Lexer('return   ' . "\t\n\t" . '   42');

        $lexer->readOneOf(TokenTypes::from(TokenType::KEYWORD_RETURN, TokenType::INTEGER_DECIMAL));
        $lexer->skipSpace();
        $lexer->readOneOf(TokenTypes::from(TokenType::KEYWORD_RETURN, TokenType::INTEGER_DECIMAL));

        $this->assertEquals(
            new Token(
                rangeInSource: self::range([1, 4], [1, 5]),
                type: TokenType::INTEGER_DECIMAL,
                value: '42'
            ),
            $lexer->getTokenUnderCursor()
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
        $lexer = new Lexer($source);

        $lexer->read(TokenType::KEYWORD_IMPORT);
        $lexer->skipSpaceAndComments();
        $lexer->read(TokenType::KEYWORD_EXPORT);
        $lexer->skipSpaceAndComments();
        $lexer->read(TokenType::KEYWORD_COMPONENT);

        $this->assertEquals(
            new Token(
                rangeInSource: self::range([6, 4], [6, 12]),
                type: TokenType::KEYWORD_COMPONENT,
                value: 'component'
            ),
            $lexer->getTokenUnderCursor()
        );

        // Multiple
        $lexer = new Lexer($source);
        $lexer->readOneOf(
            TokenTypes::from(
                TokenType::KEYWORD_IMPORT,
                TokenType::KEYWORD_EXPORT,
                TokenType::KEYWORD_COMPONENT
            )
        );
        $lexer->skipSpaceAndComments();
        $lexer->readOneOf(
            TokenTypes::from(
                TokenType::KEYWORD_IMPORT,
                TokenType::KEYWORD_EXPORT,
                TokenType::KEYWORD_COMPONENT
            )
        );
        $lexer->skipSpaceAndComments();
        $lexer->readOneOf(
            TokenTypes::from(
                TokenType::KEYWORD_IMPORT,
                TokenType::KEYWORD_EXPORT,
                TokenType::KEYWORD_COMPONENT
            )
        );

        $this->assertEquals(
            new Token(
                rangeInSource: self::range([6, 4], [6, 12]),
                type: TokenType::KEYWORD_COMPONENT,
                value: 'component'
            ),
            $lexer->getTokenUnderCursor()
        );
    }

    /**
     * @return iterable<mixed>
     */
    public static function illegalOperationsAfterFailureExamples(): iterable
    {
        yield [fn (Lexer $lexer) => $lexer->read(TokenType::KEYWORD_IMPORT)];
        yield [
            fn (Lexer $lexer) => $lexer->readOneOf(
                TokenTypes::from(
                    TokenType::KEYWORD_IMPORT,
                    TokenType::KEYWORD_NULL,
                    TokenType::SYMBOL_ARROW_SINGLE,
                    TokenType::BRACKET_ANGLE_CLOSE,
                )
            )
        ];
        yield [fn (Lexer $lexer) => $lexer->skipSpace()];
        yield [fn (Lexer $lexer) => $lexer->skipSpaceAndComments()];
        yield [fn (Lexer $lexer) => $lexer->getTokenUnderCursor()];
    }

    /**
     * @dataProvider illegalOperationsAfterFailureExamples
     * @test
     * @param callable $operation
     * @return void
     */
    public function cannotBeReusedAfterFailure(callable $operation): void
    {
        $lexer = new Lexer('import');
        try {
            $lexer->read(TokenType::SYMBOL_BOOLEAN_AND);
        } catch (LexerException $e) {
        }

        $this->expectException(AssertionError::class);
        $operation($lexer);
    }

    /**
     * @test
     */
    public function tellsIfItHasEnded(): void
    {
        $lexer = new Lexer('');

        $this->assertTrue($lexer->isEnd());

        $lexer = new Lexer('return null');

        $this->assertFalse($lexer->isEnd());

        $lexer->read(TokenType::KEYWORD_RETURN);

        $this->assertFalse($lexer->isEnd());

        $lexer->read(TokenType::SPACE);

        $this->assertFalse($lexer->isEnd());

        $lexer->read(TokenType::KEYWORD_NULL);

        $this->assertTrue($lexer->isEnd());
    }
}
