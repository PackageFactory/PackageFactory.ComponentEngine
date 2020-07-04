<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Lexer\Scope;

use PackageFactory\ComponentEngine\Parser\Lexer\Debug\Printer;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope\Afx;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;
use PackageFactory\ComponentEngine\Test\Util\TokenizerTestTrait;
use PHPUnit\Framework\TestCase;

final class AfxTest extends TestCase
{
    use TokenizerTestTrait;

    /**
     * @return array<string, array{string, array<int, array{TokenType, string}>}>
     */
    public function happyPathProvider(): array
    {
        return [
            'opening tag' => [
                '<a>',
                [
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'a'],
                    [TokenType::AFX_TAG_END(), '>'],
                ]
            ],
            'closing tag' => [
                '</a>',
                [
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::IDENTIFIER(), 'a'],
                    [TokenType::AFX_TAG_END(), '>'],
                ]
            ],
            'opening and closing tag' => [
                '<a></a>',
                [
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'a'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::IDENTIFIER(), 'a'],
                    [TokenType::AFX_TAG_END(), '>'],
                ]
            ],
            'opening and closing tag with content' => [
                '<a>Hello World!</a>',
                [
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'a'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::AFX_TAG_CONTENT(), 'Hello'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::AFX_TAG_CONTENT(), 'World!'],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::IDENTIFIER(), 'a'],
                    [TokenType::AFX_TAG_END(), '>'],
                ]
            ],
            'opening and closing tag with content and interpolation' => [
                '<a>Hello {props.name}!</a>',
                [
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'a'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::AFX_TAG_CONTENT(), 'Hello'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::AFX_EXPRESSION_START(), '{'],
                    [TokenType::IDENTIFIER(), 'props'],
                    [TokenType::PERIOD(), '.'],
                    [TokenType::IDENTIFIER(), 'name'],
                    [TokenType::AFX_EXPRESSION_END(), '}'],
                    [TokenType::AFX_TAG_CONTENT(), '!'],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::IDENTIFIER(), 'a'],
                    [TokenType::AFX_TAG_END(), '>'],
                ]
            ],
            'opening and closing tag with content and line break' => [
                '<a>Hello' . PHP_EOL . 'World!</a>',
                [
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'a'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::AFX_TAG_CONTENT(), 'Hello'],
                    [TokenType::END_OF_LINE(), PHP_EOL],
                    [TokenType::AFX_TAG_CONTENT(), 'World!'],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::IDENTIFIER(), 'a'],
                    [TokenType::AFX_TAG_END(), '>'],
                ]
            ],
            'opening and closing tag with expression attribute' => [
                '<div class={{ [styles.main]: props.type === \'main\' }}></div>',
                [
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'div'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'class'],
                    [TokenType::AFX_ATTRIBUTE_ASSIGNMENT(), '='],
                    [TokenType::AFX_EXPRESSION_START(), '{'],
                    [TokenType::BRACKETS_CURLY_OPEN(), '{'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::BRACKETS_SQUARE_OPEN(), '['],
                    [TokenType::IDENTIFIER(), 'styles'],
                    [TokenType::PERIOD(), '.'],
                    [TokenType::IDENTIFIER(), 'main'],
                    [TokenType::BRACKETS_SQUARE_CLOSE(), ']'],
                    [TokenType::COLON(), ':'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'props'],
                    [TokenType::PERIOD(), '.'],
                    [TokenType::IDENTIFIER(), 'type'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::COMPARATOR_EQ(), '==='],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::STRING_LITERAL_START(), '\''],
                    [TokenType::STRING_LITERAL_CONTENT(), 'main'],
                    [TokenType::STRING_LITERAL_END(), '\''],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::BRACKETS_CURLY_CLOSE(), '}'],
                    [TokenType::AFX_EXPRESSION_END(), '}'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::IDENTIFIER(), 'div'],
                    [TokenType::AFX_TAG_END(), '>'],
                ]
            ],
            'opening and closing tag with interpolated expression content' => [
                '<a>Hello {props.firstName + \' \' + props.lastName}!</a>',
                [
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'a'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::AFX_TAG_CONTENT(), 'Hello'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::AFX_EXPRESSION_START(), '{'],
                    [TokenType::IDENTIFIER(), 'props'],
                    [TokenType::PERIOD(), '.'],
                    [TokenType::IDENTIFIER(), 'firstName'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::OPERATOR_ADD(), '+'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::STRING_LITERAL_START(), '\''],
                    [TokenType::STRING_LITERAL_CONTENT(), ' '],
                    [TokenType::STRING_LITERAL_END(), '\''],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::OPERATOR_ADD(), '+'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'props'],
                    [TokenType::PERIOD(), '.'],
                    [TokenType::IDENTIFIER(), 'lastName'],
                    [TokenType::AFX_EXPRESSION_END(), '}'],
                    [TokenType::AFX_TAG_CONTENT(), '!'],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::IDENTIFIER(), 'a'],
                    [TokenType::AFX_TAG_END(), '>'],
                ]
            ],
            'nested tags' => [
                '<a><b><c></c></b></a>',
                [
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'a'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'b'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'c'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::IDENTIFIER(), 'c'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::IDENTIFIER(), 'b'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::IDENTIFIER(), 'a'],
                    [TokenType::AFX_TAG_END(), '>'],
                ]
            ],
            'nested tags with content' => [
                '<a>Before B<b>Before C<c>Hello World!</c>After C</b>After B</a>',
                [
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'a'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::AFX_TAG_CONTENT(), 'Before'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::AFX_TAG_CONTENT(), 'B'],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'b'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::AFX_TAG_CONTENT(), 'Before'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::AFX_TAG_CONTENT(), 'C'],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'c'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::AFX_TAG_CONTENT(), 'Hello'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::AFX_TAG_CONTENT(), 'World!'],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::IDENTIFIER(), 'c'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::AFX_TAG_CONTENT(), 'After'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::AFX_TAG_CONTENT(), 'C'],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::IDENTIFIER(), 'b'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::AFX_TAG_CONTENT(), 'After'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::AFX_TAG_CONTENT(), 'B'],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::IDENTIFIER(), 'a'],
                    [TokenType::AFX_TAG_END(), '>'],
                ]
            ],
            'self-closing tag' => [
                '<br/>',
                [
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'br'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::AFX_TAG_END(), '>'],
                ]
            ],
            'self-closing tag with attribute' => [
                '<input type="text"/>',
                [
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'input'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'type'],
                    [TokenType::AFX_ATTRIBUTE_ASSIGNMENT(), '='],
                    [TokenType::STRING_LITERAL_START(), '"'],
                    [TokenType::STRING_LITERAL_CONTENT(), 'text'],
                    [TokenType::STRING_LITERAL_END(), '"'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::AFX_TAG_END(), '>'],
                ]
            ],
            'self-closing tag with multiple attributes' => [
                '<input type="text" value="Hello World!"/>',
                [
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'input'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'type'],
                    [TokenType::AFX_ATTRIBUTE_ASSIGNMENT(), '='],
                    [TokenType::STRING_LITERAL_START(), '"'],
                    [TokenType::STRING_LITERAL_CONTENT(), 'text'],
                    [TokenType::STRING_LITERAL_END(), '"'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'value'],
                    [TokenType::AFX_ATTRIBUTE_ASSIGNMENT(), '='],
                    [TokenType::STRING_LITERAL_START(), '"'],
                    [TokenType::STRING_LITERAL_CONTENT(), 'Hello World!'],
                    [TokenType::STRING_LITERAL_END(), '"'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::AFX_TAG_END(), '>'],
                ]
            ],
            'self-closing tag with expression attributes' => [
                '<input type={props.isEmail ? \'email\' : \'text\'} value={props.value}/>',
                [
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'input'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'type'],
                    [TokenType::AFX_ATTRIBUTE_ASSIGNMENT(), '='],
                    [TokenType::AFX_EXPRESSION_START(), '{'],
                    [TokenType::IDENTIFIER(), 'props'],
                    [TokenType::PERIOD(), '.'],
                    [TokenType::IDENTIFIER(), 'isEmail'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::QUESTIONMARK(), '?'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::STRING_LITERAL_START(), '\''],
                    [TokenType::STRING_LITERAL_CONTENT(), 'email'],
                    [TokenType::STRING_LITERAL_END(), '\''],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::COLON(), ':'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::STRING_LITERAL_START(), '\''],
                    [TokenType::STRING_LITERAL_CONTENT(), 'text'],
                    [TokenType::STRING_LITERAL_END(), '\''],
                    [TokenType::AFX_EXPRESSION_END(), '}'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'value'],
                    [TokenType::AFX_ATTRIBUTE_ASSIGNMENT(), '='],
                    [TokenType::AFX_EXPRESSION_START(), '{'],
                    [TokenType::IDENTIFIER(), 'props'],
                    [TokenType::PERIOD(), '.'],
                    [TokenType::IDENTIFIER(), 'value'],
                    [TokenType::AFX_EXPRESSION_END(), '}'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::AFX_TAG_END(), '>'],
                ]
            ],
            'self-closing tag with spread' => [
                '<input {...props}/>',
                [
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'input'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::AFX_EXPRESSION_START(), '{'],
                    [TokenType::OPERATOR_SPREAD(), '...'],
                    [TokenType::IDENTIFIER(), 'props'],
                    [TokenType::AFX_EXPRESSION_END(), '}'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::AFX_TAG_END(), '>'],
                ]
            ],
            'self-closing tag with multiple spreads' => [
                '<input {...props} {...rest}/>',
                [
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'input'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::AFX_EXPRESSION_START(), '{'],
                    [TokenType::OPERATOR_SPREAD(), '...'],
                    [TokenType::IDENTIFIER(), 'props'],
                    [TokenType::AFX_EXPRESSION_END(), '}'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::AFX_EXPRESSION_START(), '{'],
                    [TokenType::OPERATOR_SPREAD(), '...'],
                    [TokenType::IDENTIFIER(), 'rest'],
                    [TokenType::AFX_EXPRESSION_END(), '}'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::AFX_TAG_END(), '>'],
                ]
            ],
            'fragment' => [
                '<>This is a fragment</>',
                [
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::AFX_TAG_CONTENT(), 'This'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::AFX_TAG_CONTENT(), 'is'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::AFX_TAG_CONTENT(), 'a'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::AFX_TAG_CONTENT(), 'fragment'],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::AFX_TAG_END(), '>'],
                ]
            ],
            'whitespace' => [
                '<a>Whitespace     does not    matter</a>',
                [
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'a'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::AFX_TAG_CONTENT(), 'Whitespace'],
                    [TokenType::WHITESPACE(), '     '],
                    [TokenType::AFX_TAG_CONTENT(), 'does'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::AFX_TAG_CONTENT(), 'not'],
                    [TokenType::WHITESPACE(), '    '],
                    [TokenType::AFX_TAG_CONTENT(), 'matter'],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::IDENTIFIER(), 'a'],
                    [TokenType::AFX_TAG_END(), '>'],
                ]
            ],
            'tag starting with dollar sign' => [
                '<$tag>Some Content</$tag>',
                [
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), '$tag'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::AFX_TAG_CONTENT(), 'Some'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::AFX_TAG_CONTENT(), 'Content'],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::IDENTIFIER(), '$tag'],
                    [TokenType::AFX_TAG_END(), '>'],
                ]
            ],
            'tag starting with underscore' => [
                '<_tag>Some Content</_tag>',
                [
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), '_tag'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::AFX_TAG_CONTENT(), 'Some'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::AFX_TAG_CONTENT(), 'Content'],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::IDENTIFIER(), '_tag'],
                    [TokenType::AFX_TAG_END(), '>'],
                ]
            ],
        ];
    }

    /**
     * @dataProvider happyPathProvider
     * @test
     * @small
     * @param string $input
     * @param array<int, array{TokenType, string}> $tokens
     * @return void
     */
    public function testHappyPath(string $input, array $tokens): void
    {
        $iterator = SourceIterator::fromSource(Source::fromString($input));
        $this->assertTokenStream($tokens, Afx::tokenize($iterator));
    }

    /**
     * @return array<string, array{string, array<int, array{TokenType, string}>}>
     */
    public function exitPathProvider(): array
    {
        return [
            'unknown symbols within tag' => [
                '<a %%%>',
                [
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'a'],
                    [TokenType::WHITESPACE(), ' '],
                ]
            ],
        ];
    }

    /**
     * @dataProvider exitPathProvider
     * @test
     * @small
     * @param string $input
     * @param array<int, array{TokenType, string}> $tokens
     * @return void
     */
    public function testExitPath(string $input, array $tokens): void
    {
        $iterator = SourceIterator::fromSource(Source::fromString($input));
        $this->assertTokenStream($tokens, Afx::tokenize($iterator));
    }
}