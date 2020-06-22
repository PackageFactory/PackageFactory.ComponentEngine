<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Lexer\Scope;

use PackageFactory\ComponentEngine\Parser\Lexer\Debug\Printer;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope\Expression;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;
use PackageFactory\ComponentEngine\Test\Util\TokenizerTestTrait;
use PHPUnit\Framework\TestCase;

final class ExpressionTest extends TestCase
{
    use TokenizerTestTrait;

    /**
     * @return array<string, array{string, array<int, array{TokenType, string}>}>
     */
    public function happyPathProvider(): array
    {
        return [
            'boolean true' => [
                'true',
                [
                    [TokenType::KEYWORD_TRUE(), 'true']
                ]
            ],
            'boolean false' => [
                'false',
                [
                    [TokenType::KEYWORD_FALSE(), 'false']
                ]
            ],
            'null' => [
                'null',
                [
                    [TokenType::KEYWORD_NULL(), 'null']
                ]
            ],
            'identifier' => [
                'foo',
                [
                    [TokenType::IDENTIFIER(), 'foo']
                ]
            ],
            'identifier chain' => [
                'foo.bar.baz',
                [
                    [TokenType::IDENTIFIER(), 'foo'],
                    [TokenType::PERIOD(), '.'],
                    [TokenType::IDENTIFIER(), 'bar'],
                    [TokenType::PERIOD(), '.'],
                    [TokenType::IDENTIFIER(), 'baz']
                ]
            ],
            'identifier chain with array access' => [
                'foo[bar].baz',
                [
                    [TokenType::IDENTIFIER(), 'foo'],
                    [TokenType::BRACKETS_SQUARE_OPEN(), '['],
                    [TokenType::IDENTIFIER(), 'bar'],
                    [TokenType::BRACKETS_SQUARE_CLOSE(), ']'],
                    [TokenType::PERIOD(), '.'],
                    [TokenType::IDENTIFIER(), 'baz']
                ]
            ],
            'identifier chain with array access and string key' => [
                'foo[\'bar\'].baz',
                [
                    [TokenType::IDENTIFIER(), 'foo'],
                    [TokenType::BRACKETS_SQUARE_OPEN(), '['],
                    [TokenType::STRING_LITERAL_START(), '\''],
                    [TokenType::STRING_LITERAL_CONTENT(), 'bar'],
                    [TokenType::STRING_LITERAL_END(), '\''],
                    [TokenType::BRACKETS_SQUARE_CLOSE(), ']'],
                    [TokenType::PERIOD(), '.'],
                    [TokenType::IDENTIFIER(), 'baz']
                ]
            ],
            'optional chain at start' => [
                'foo?.bar.baz',
                [
                    [TokenType::IDENTIFIER(), 'foo'],
                    [TokenType::OPERATOR_OPTCHAIN(), '?.'],
                    [TokenType::IDENTIFIER(), 'bar'],
                    [TokenType::PERIOD(), '.'],
                    [TokenType::IDENTIFIER(), 'baz']
                ]
            ],
            'optional chain mid-term' => [
                'foo.bar?.baz',
                [
                    [TokenType::IDENTIFIER(), 'foo'],
                    [TokenType::PERIOD(), '.'],
                    [TokenType::IDENTIFIER(), 'bar'],
                    [TokenType::OPERATOR_OPTCHAIN(), '?.'],
                    [TokenType::IDENTIFIER(), 'baz']
                ]
            ],
            'operation: not' => [
                '!foo',
                [
                    [TokenType::OPERATOR_LOGICAL_NOT(), '!'],
                    [TokenType::IDENTIFIER(), 'foo'],
                ]
            ],
            'operation: and' => [
                'foo && bar',
                [
                    [TokenType::IDENTIFIER(), 'foo'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::OPERATOR_LOGICAL_AND(), '&&'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'bar'],
                ]
            ],
            'operation: or' => [
                'foo || bar',
                [
                    [TokenType::IDENTIFIER(), 'foo'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::OPERATOR_LOGICAL_OR(), '||'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'bar'],
                ]
            ],
            'operation: nullish coalescing' => [
                'foo ?? bar',
                [
                    [TokenType::IDENTIFIER(), 'foo'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::OPERATOR_NULLISH_COALESCE(), '??'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'bar'],
                ]
            ],
            'operation: spread' => [
                '...foo',
                [
                    [TokenType::OPERATOR_SPREAD(), '...'],
                    [TokenType::IDENTIFIER(), 'foo'],
                ]
            ],
            'operation: addition' => [
                'foo + bar',
                [
                    [TokenType::IDENTIFIER(), 'foo'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::OPERATOR_ADD(), '+'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'bar'],
                ]
            ],
            'operation: subtraction' => [
                'foo - bar',
                [
                    [TokenType::IDENTIFIER(), 'foo'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::OPERATOR_SUBTRACT(), '-'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'bar'],
                ]
            ],
            'operation: multiplication' => [
                'foo * bar',
                [
                    [TokenType::IDENTIFIER(), 'foo'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::OPERATOR_MULTIPLY(), '*'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'bar'],
                ]
            ],
            'operation: division' => [
                'foo / bar',
                [
                    [TokenType::IDENTIFIER(), 'foo'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::OPERATOR_DIVIDE(), '/'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'bar'],
                ]
            ],
            'operation: modulo' => [
                'foo % bar',
                [
                    [TokenType::IDENTIFIER(), 'foo'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::OPERATOR_MODULO(), '%'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'bar'],
                ]
            ],
            'comparison: equality' => [
                'foo === bar',
                [
                    [TokenType::IDENTIFIER(), 'foo'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::COMPARATOR_EQ(), '==='],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'bar'],
                ]
            ],
            'comparison: greater than' => [
                'foo > bar',
                [
                    [TokenType::IDENTIFIER(), 'foo'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::COMPARATOR_GT(), '>'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'bar'],
                ]
            ],
            'comparison: greater than or equal to' => [
                'foo >= bar',
                [
                    [TokenType::IDENTIFIER(), 'foo'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::COMPARATOR_GTE(), '>='],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'bar'],
                ]
            ],
            'comparison: less than' => [
                'foo < bar',
                [
                    [TokenType::IDENTIFIER(), 'foo'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::COMPARATOR_LT(), '<'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'bar'],
                ]
            ],
            'comparison: less than or equal to' => [
                'foo <= bar',
                [
                    [TokenType::IDENTIFIER(), 'foo'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::COMPARATOR_LTE(), '<='],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'bar'],
                ]
            ],
            'term' => [
                '!(a + 12.3 - (27 % foo) <= xyz / .15) * 0xFF',
                [
                    [TokenType::OPERATOR_LOGICAL_NOT(), '!'],
                    [TokenType::BRACKETS_ROUND_OPEN(), '('],
                    [TokenType::IDENTIFIER(), 'a'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::OPERATOR_ADD(), '+'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::NUMBER(), '12.3'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::OPERATOR_SUBTRACT(), '-'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::BRACKETS_ROUND_OPEN(), '('],
                    [TokenType::NUMBER(), '27'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::OPERATOR_MODULO(), '%'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'foo'],
                    [TokenType::BRACKETS_ROUND_CLOSE(), ')'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::COMPARATOR_LTE(), '<='],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'xyz'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::OPERATOR_DIVIDE(), '/'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::NUMBER(), '.15'],
                    [TokenType::BRACKETS_ROUND_CLOSE(), ')'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::OPERATOR_MULTIPLY(), '*'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::NUMBER(), '0xFF'],
                ]
            ],
            'object literal' => [
                '{ key: \'value\', foo: 12, bar: baz }',
                [
                    [TokenType::BRACKETS_CURLY_OPEN(), '{'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'key'],
                    [TokenType::COLON(), ':'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::STRING_LITERAL_START(), '\''],
                    [TokenType::STRING_LITERAL_CONTENT(), 'value'],
                    [TokenType::STRING_LITERAL_END(), '\''],
                    [TokenType::COMMA(), ','],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'foo'],
                    [TokenType::COLON(), ':'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::NUMBER(), '12'],
                    [TokenType::COMMA(), ','],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'bar'],
                    [TokenType::COLON(), ':'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'baz'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::BRACKETS_CURLY_CLOSE(), '}'],
                ]
            ],
            'array literal' => [
                '[10, foo, \'bar\']',
                [
                    [TokenType::BRACKETS_SQUARE_OPEN(), '['],
                    [TokenType::NUMBER(), '10'],
                    [TokenType::COMMA(), ','],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'foo'],
                    [TokenType::COMMA(), ','],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::STRING_LITERAL_START(), '\''],
                    [TokenType::STRING_LITERAL_CONTENT(), 'bar'],
                    [TokenType::STRING_LITERAL_END(), '\''],
                    [TokenType::BRACKETS_SQUARE_CLOSE(), ']'],
                ]
            ],
            'object literal with spread' => [
                '{ key: \'value\', ...props, foo: 12, bar: baz, ...rest }',
                [
                    [TokenType::BRACKETS_CURLY_OPEN(), '{'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'key'],
                    [TokenType::COLON(), ':'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::STRING_LITERAL_START(), '\''],
                    [TokenType::STRING_LITERAL_CONTENT(), 'value'],
                    [TokenType::STRING_LITERAL_END(), '\''],
                    [TokenType::COMMA(), ','],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::OPERATOR_SPREAD(), '...'],
                    [TokenType::IDENTIFIER(), 'props'],
                    [TokenType::COMMA(), ','],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'foo'],
                    [TokenType::COLON(), ':'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::NUMBER(), '12'],
                    [TokenType::COMMA(), ','],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'bar'],
                    [TokenType::COLON(), ':'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'baz'],
                    [TokenType::COMMA(), ','],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::OPERATOR_SPREAD(), '...'],
                    [TokenType::IDENTIFIER(), 'rest'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::BRACKETS_CURLY_CLOSE(), '}'],
                ]
            ],
            'array literal with spread' => [
                '[10, foo, ...list, \'bar\', ...rest]',
                [
                    [TokenType::BRACKETS_SQUARE_OPEN(), '['],
                    [TokenType::NUMBER(), '10'],
                    [TokenType::COMMA(), ','],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'foo'],
                    [TokenType::COMMA(), ','],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::OPERATOR_SPREAD(), '...'],
                    [TokenType::IDENTIFIER(), 'list'],
                    [TokenType::COMMA(), ','],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::STRING_LITERAL_START(), '\''],
                    [TokenType::STRING_LITERAL_CONTENT(), 'bar'],
                    [TokenType::STRING_LITERAL_END(), '\''],
                    [TokenType::COMMA(), ','],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::OPERATOR_SPREAD(), '...'],
                    [TokenType::IDENTIFIER(), 'rest'],
                    [TokenType::BRACKETS_SQUARE_CLOSE(), ']'],
                ]
            ],
            'object literal with computed keys' => [
                '{ [props.key]: \'value\', foo: 12, [styles.bar[`${props.type}-0`]]: baz }',
                [
                    [TokenType::BRACKETS_CURLY_OPEN(), '{'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::BRACKETS_SQUARE_OPEN(), '['],
                    [TokenType::IDENTIFIER(), 'props'],
                    [TokenType::PERIOD(), '.'],
                    [TokenType::IDENTIFIER(), 'key'],
                    [TokenType::BRACKETS_SQUARE_CLOSE(), ']'],
                    [TokenType::COLON(), ':'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::STRING_LITERAL_START(), '\''],
                    [TokenType::STRING_LITERAL_CONTENT(), 'value'],
                    [TokenType::STRING_LITERAL_END(), '\''],
                    [TokenType::COMMA(), ','],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'foo'],
                    [TokenType::COLON(), ':'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::NUMBER(), '12'],
                    [TokenType::COMMA(), ','],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::BRACKETS_SQUARE_OPEN(), '['],
                    [TokenType::IDENTIFIER(), 'styles'],
                    [TokenType::PERIOD(), '.'],
                    [TokenType::IDENTIFIER(), 'bar'],
                    [TokenType::BRACKETS_SQUARE_OPEN(), '['],
                    [TokenType::TEMPLATE_LITERAL_START(), '`'],
                    [TokenType::TEMPLATE_LITERAL_INTERPOLATION_START(), '${'],
                    [TokenType::IDENTIFIER(), 'props'],
                    [TokenType::PERIOD(), '.'],
                    [TokenType::IDENTIFIER(), 'type'],
                    [TokenType::TEMPLATE_LITERAL_INTERPOLATION_END(), '}'],
                    [TokenType::TEMPLATE_LITERAL_CONTENT(), '-0'],
                    [TokenType::TEMPLATE_LITERAL_END(), '`'],
                    [TokenType::BRACKETS_SQUARE_CLOSE(), ']'],
                    [TokenType::BRACKETS_SQUARE_CLOSE(), ']'],
                    [TokenType::COLON(), ':'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'baz'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::BRACKETS_CURLY_CLOSE(), '}'],
                ]
            ],
            'string literal (double-quote)' => [
                '"Hello World"',
                [
                    [TokenType::STRING_LITERAL_START(), '"'],
                    [TokenType::STRING_LITERAL_CONTENT(), 'Hello World'],
                    [TokenType::STRING_LITERAL_END(), '"'],
                ]
            ],
            'string literal (single-quote)' => [
                '\'Hello World\'',
                [
                    [TokenType::STRING_LITERAL_START(), '\''],
                    [TokenType::STRING_LITERAL_CONTENT(), 'Hello World'],
                    [TokenType::STRING_LITERAL_END(), '\''],
                ]
            ],
            'template literal' => [
                '`Hello World`',
                [
                    [TokenType::TEMPLATE_LITERAL_START(), '`'],
                    [TokenType::TEMPLATE_LITERAL_CONTENT(), 'Hello World'],
                    [TokenType::TEMPLATE_LITERAL_END(), '`'],
                ]
            ],
            'template literal with interpolation' => [
                '`Hello ${props.name}!`',
                [
                    [TokenType::TEMPLATE_LITERAL_START(), '`'],
                    [TokenType::TEMPLATE_LITERAL_CONTENT(), 'Hello '],
                    [TokenType::TEMPLATE_LITERAL_INTERPOLATION_START(), '${'],
                    [TokenType::IDENTIFIER(), 'props'],
                    [TokenType::PERIOD(), '.'],
                    [TokenType::IDENTIFIER(), 'name'],
                    [TokenType::TEMPLATE_LITERAL_INTERPOLATION_END(), '}'],
                    [TokenType::TEMPLATE_LITERAL_CONTENT(), '!'],
                    [TokenType::TEMPLATE_LITERAL_END(), '`'],
                ]
            ],
            'comment' => [
                '/* This is a comment */',
                [
                    [TokenType::COMMENT_START(), '/*'],
                    [TokenType::COMMENT_CONTENT(), ' This is a comment '],
                    [TokenType::COMMENT_END(), '*/'],
                ]
            ],
            'comment - expression is ignored' => [
                '/* This is a comment {the.expression + is.ignored} */',
                [
                    [TokenType::COMMENT_START(), '/*'],
                    [TokenType::COMMENT_CONTENT(), ' This is a comment {the.expression + is.ignored} '],
                    [TokenType::COMMENT_END(), '*/'],
                ]
            ],
        ];
    }

    /**
     * @dataProvider happyPathProvider
     * @test
     * @param string $input
     * @param array<int, array{TokenType, string}> $tokens
     * @return void
     */
    public function testHappyPath(string $input, array $tokens): void
    {
        $iterator = SourceIterator::createFromSource(Source::createFromString($input));
        $this->assertTokenStream($tokens, Expression::tokenize($iterator));
    }

    /**
     * @return array<string, array{string}>
     */
    public function exceptionPathProvider(): array
    {
        return [
            'unterminated logical and operator' => [
                'true & false'
            ],
            'unterminated logical or operator' => [
                'true | false'
            ],
            'unterminated referential equality comparator (1)' => [
                '42 = 42'
            ],
            'unterminated referential equality comparator (2)' => [
                '42 == 42'
            ],
        ];
    }

    /**
     * @dataProvider exceptionPathProvider
     * @test
     * @param string $input
     * @return void
     */
    public function testExceptionPath(string $input): void
    {
        $this->expectException(\Exception::class);
        $iterator = SourceIterator::createFromSource(Source::createFromString($input));
        iterator_to_array(Expression::tokenize($iterator));
    }

    /**
     * @return array<string, array{string, array<int, array{TokenType, string}>}>
     */
    public function exitPathProvider(): array
    {
        return [
            'Something~else' => [
                'Something~',
                [
                    [TokenType::IDENTIFIER(), 'Something']
                ]
            ],
            'Something#else' => [
                'Something#else',
                [
                    [TokenType::IDENTIFIER(), 'Something']
                ]
            ],
            'Something§else' => [
                'Something§else',
                [
                    [TokenType::IDENTIFIER(), 'Something']
                ]
            ],
            'curly bracket sequence with an extra closing bracket' => [
                '{{}}}else',
                [
                    [TokenType::BRACKETS_CURLY_OPEN(), '{'],
                    [TokenType::BRACKETS_CURLY_OPEN(), '{'],
                    [TokenType::BRACKETS_CURLY_CLOSE(), '}'],
                    [TokenType::BRACKETS_CURLY_CLOSE(), '}'],
                ]
            ],
            'square bracket sequence with an extra closing bracket' => [
                '[[[]]]]else',
                [
                    [TokenType::BRACKETS_SQUARE_OPEN(), '['],
                    [TokenType::BRACKETS_SQUARE_OPEN(), '['],
                    [TokenType::BRACKETS_SQUARE_OPEN(), '['],
                    [TokenType::BRACKETS_SQUARE_CLOSE(), ']'],
                    [TokenType::BRACKETS_SQUARE_CLOSE(), ']'],
                    [TokenType::BRACKETS_SQUARE_CLOSE(), ']'],
                ]
            ],
            'round bracket sequence with an extra closing bracket' => [
                '(((()))))else',
                [
                    [TokenType::BRACKETS_ROUND_OPEN(), '('],
                    [TokenType::BRACKETS_ROUND_OPEN(), '('],
                    [TokenType::BRACKETS_ROUND_OPEN(), '('],
                    [TokenType::BRACKETS_ROUND_OPEN(), '('],
                    [TokenType::BRACKETS_ROUND_CLOSE(), ')'],
                    [TokenType::BRACKETS_ROUND_CLOSE(), ')'],
                    [TokenType::BRACKETS_ROUND_CLOSE(), ')'],
                    [TokenType::BRACKETS_ROUND_CLOSE(), ')'],
                ]
            ],
        ];
    }

    /**
     * @dataProvider exitPathProvider
     * @test
     * @param string $input
     * @param array<int, array{TokenType, string}> $tokens
     * @return void
     */
    public function testExitPath(string $input, array $tokens): void
    {
        $iterator = SourceIterator::createFromSource(Source::createFromString($input));
        $this->assertTokenStream($tokens, Expression::tokenize($iterator));
    }
}