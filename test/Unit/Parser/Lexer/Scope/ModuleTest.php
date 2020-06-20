<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Lexer\Scope;

use PackageFactory\ComponentEngine\Parser\Lexer\Scope\Module;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;
use PackageFactory\ComponentEngine\Test\Util\TokenizerTestTrait;
use PHPUnit\Framework\TestCase;

final class ModuleTest extends TestCase
{
    use TokenizerTestTrait;

    /**
     * @return array<string, array<int, string>>
     */
    public function provider(): array
    {
        return [
            'import default module' => [
                'import Button from "./Button.afx"',
                [
                    [TokenType::MODULE_KEYWORD_IMPORT(), 'import'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'Button'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_KEYWORD_FROM(), 'from'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::STRING_LITERAL_START(), '"'],
                    [TokenType::STRING_LITERAL_CONTENT(), './Button.afx'],
                    [TokenType::STRING_LITERAL_END(), '"'],
                ]
            ],
            'import named module' => [
                'import { Header as MainHeader } from "./Header.afx"',
                [
                    [TokenType::MODULE_KEYWORD_IMPORT(), 'import'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::BRACKETS_CURLY_OPEN(), '{'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'Header'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_KEYWORD_AS(), 'as'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'MainHeader'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::BRACKETS_CURLY_CLOSE(), '}'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_KEYWORD_FROM(), 'from'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::STRING_LITERAL_START(), '"'],
                    [TokenType::STRING_LITERAL_CONTENT(), './Header.afx'],
                    [TokenType::STRING_LITERAL_END(), '"'],
                ]
            ],
            'import multiple named modules' => [
                'import { Input, Select, TextArea } from "./FormElements.afx"',
                [
                    [TokenType::MODULE_KEYWORD_IMPORT(), 'import'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::BRACKETS_CURLY_OPEN(), '{'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'Input'],
                    [TokenType::COMMA(), ','],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'Select'],
                    [TokenType::COMMA(), ','],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'TextArea'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::BRACKETS_CURLY_CLOSE(), '}'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_KEYWORD_FROM(), 'from'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::STRING_LITERAL_START(), '"'],
                    [TokenType::STRING_LITERAL_CONTENT(), './FormElements.afx'],
                    [TokenType::STRING_LITERAL_END(), '"'],
                ]
            ],
            'import wildcard' => [
                'import * as SelectBox from "./SelectBox.afx"',
                [
                    [TokenType::MODULE_KEYWORD_IMPORT(), 'import'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_WILDCARD(), '*'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_KEYWORD_AS(), 'as'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'SelectBox'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_KEYWORD_FROM(), 'from'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::STRING_LITERAL_START(), '"'],
                    [TokenType::STRING_LITERAL_CONTENT(), './SelectBox.afx'],
                    [TokenType::STRING_LITERAL_END(), '"'],
                ]
            ],
            'import name starting with "as"' => [
                'import ast from "./Ast.afx"',
                [
                    [TokenType::MODULE_KEYWORD_IMPORT(), 'import'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'ast'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_KEYWORD_FROM(), 'from'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::STRING_LITERAL_START(), '"'],
                    [TokenType::STRING_LITERAL_CONTENT(), './Ast.afx'],
                    [TokenType::STRING_LITERAL_END(), '"'],
                ]
            ],
            'import name starting with "from"' => [
                'import frommage from "./Cheese.afx"',
                [
                    [TokenType::MODULE_KEYWORD_IMPORT(), 'import'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'frommage'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_KEYWORD_FROM(), 'from'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::STRING_LITERAL_START(), '"'],
                    [TokenType::STRING_LITERAL_CONTENT(), './Cheese.afx'],
                    [TokenType::STRING_LITERAL_END(), '"'],
                ]
            ],
            'const assignment' => [
                'const PI =',
                [
                    [TokenType::MODULE_KEYWORD_CONST(), 'const'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'PI'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_ASSIGNMENT(), '='],
                ]
            ],
            'const with identifier "from"' => [
                'const from =',
                [
                    [TokenType::MODULE_KEYWORD_CONST(), 'const'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'from'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_ASSIGNMENT(), '='],
                ]
            ],
            'const with identifier "as"' => [
                'const as =',
                [
                    [TokenType::MODULE_KEYWORD_CONST(), 'const'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'as'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_ASSIGNMENT(), '='],
                ]
            ],
            'const with identifier starting with "import"' => [
                'const importName =',
                [
                    [TokenType::MODULE_KEYWORD_CONST(), 'const'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'importName'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_ASSIGNMENT(), '='],
                ]
            ],
            'const with identifier starting with "export"' => [
                'const exportName =',
                [
                    [TokenType::MODULE_KEYWORD_CONST(), 'const'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'exportName'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_ASSIGNMENT(), '='],
                ]
            ],
            'const with array destructuring' => [
                'const [a, b] =',
                [
                    [TokenType::MODULE_KEYWORD_CONST(), 'const'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::BRACKETS_SQUARE_OPEN(), '['],
                    [TokenType::IDENTIFIER(), 'a'],
                    [TokenType::COMMA(), ','],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'b'],
                    [TokenType::BRACKETS_SQUARE_CLOSE(), ']'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_ASSIGNMENT(), '='],
                ]
            ],
            'const with array destructuring and spread' => [
                'const [a, ...rest] =',
                [
                    [TokenType::MODULE_KEYWORD_CONST(), 'const'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::BRACKETS_SQUARE_OPEN(), '['],
                    [TokenType::IDENTIFIER(), 'a'],
                    [TokenType::COMMA(), ','],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::OPERATOR_SPREAD(), '...'],
                    [TokenType::IDENTIFIER(), 'rest'],
                    [TokenType::BRACKETS_SQUARE_CLOSE(), ']'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_ASSIGNMENT(), '='],
                ]
            ],
            'const with object destructuring' => [
                'const { a, b } =',
                [
                    [TokenType::MODULE_KEYWORD_CONST(), 'const'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::BRACKETS_CURLY_OPEN(), '{'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'a'],
                    [TokenType::COMMA(), ','],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'b'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::BRACKETS_CURLY_CLOSE(), '}'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_ASSIGNMENT(), '='],
                ]
            ],
            'const with object destructuring and spread' => [
                'const { a, ...rest } =',
                [
                    [TokenType::MODULE_KEYWORD_CONST(), 'const'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::BRACKETS_CURLY_OPEN(), '{'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'a'],
                    [TokenType::COMMA(), ','],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::OPERATOR_SPREAD(), '...'],
                    [TokenType::IDENTIFIER(), 'rest'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::BRACKETS_CURLY_CLOSE(), '}'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_ASSIGNMENT(), '='],
                ]
            ],
            'export const' => [
                'export const foo =',
                [
                    [TokenType::MODULE_KEYWORD_EXPORT(), 'export'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_KEYWORD_CONST(), 'const'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'foo'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_ASSIGNMENT(), '='],
                ]
            ],
            'export default' => [
                'export default ()',
                [
                    [TokenType::MODULE_KEYWORD_EXPORT(), 'export'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_KEYWORD_DEFAULT(), 'default'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::BRACKETS_ROUND_OPEN(), '('],
                    [TokenType::BRACKETS_ROUND_CLOSE(), ')'],
                ]
            ],
            'expression assignment: const' => [
                'const color = 0xFF0000',
                [
                    [TokenType::MODULE_KEYWORD_CONST(), 'const'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'color'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_ASSIGNMENT(), '='],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::NUMBER(), '0xFF0000'],
                ]
            ],
            'expression assignment: multiple const' => [
                'const color = 0xFF0000' . PHP_EOL . 
                'const backgroundColor = 0x0000FF',
                [
                    [TokenType::MODULE_KEYWORD_CONST(), 'const'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'color'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_ASSIGNMENT(), '='],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::NUMBER(), '0xFF0000'],
                    [TokenType::END_OF_LINE(), PHP_EOL],
                    [TokenType::MODULE_KEYWORD_CONST(), 'const'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'backgroundColor'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_ASSIGNMENT(), '='],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::NUMBER(), '0x0000FF'],
                ]
            ],
            'expression assignment: export const' => [
                'export const name = "Jane Doe"',
                [
                    [TokenType::MODULE_KEYWORD_EXPORT(), 'export'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_KEYWORD_CONST(), 'const'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'name'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_ASSIGNMENT(), '='],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::STRING_LITERAL_START(), '"'],
                    [TokenType::STRING_LITERAL_CONTENT(), 'Jane Doe'],
                    [TokenType::STRING_LITERAL_END(), '"'],
                ]
            ],
            'expression assignment: export default' => [
                'export default { key: "foo", value: "bar" }',
                [
                    [TokenType::MODULE_KEYWORD_EXPORT(), 'export'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_KEYWORD_DEFAULT(), 'default'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::BRACKETS_CURLY_OPEN(), '{'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'key'],
                    [TokenType::COLON(), ':'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::STRING_LITERAL_START(), '"'],
                    [TokenType::STRING_LITERAL_CONTENT(), 'foo'],
                    [TokenType::STRING_LITERAL_END(), '"'],
                    [TokenType::COMMA(), ','],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'value'],
                    [TokenType::COLON(), ':'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::STRING_LITERAL_START(), '"'],
                    [TokenType::STRING_LITERAL_CONTENT(), 'bar'],
                    [TokenType::STRING_LITERAL_END(), '"'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::BRACKETS_CURLY_CLOSE(), '}'],
                ]
            ],
            'afx assignment: const' => [
                'const Button = <button>Click here!</button>',
                [
                    [TokenType::MODULE_KEYWORD_CONST(), 'const'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'Button'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_ASSIGNMENT(), '='],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'button'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::AFX_TAG_CONTENT(), 'Click here!'],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::IDENTIFIER(), 'button'],
                    [TokenType::AFX_TAG_END(), '>'],
                ]
            ],
            'afx assignment: multiple const' => [
                'const Button = <button>Click here!</button>' . PHP_EOL . 
                'const Headline = <h1>Hello World!</h1>',
                [
                    [TokenType::MODULE_KEYWORD_CONST(), 'const'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'Button'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_ASSIGNMENT(), '='],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'button'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::AFX_TAG_CONTENT(), 'Click here!'],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::IDENTIFIER(), 'button'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::END_OF_LINE(), PHP_EOL],
                    [TokenType::MODULE_KEYWORD_CONST(), 'const'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'Headline'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_ASSIGNMENT(), '='],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'h1'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::AFX_TAG_CONTENT(), 'Hello World!'],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::IDENTIFIER(), 'h1'],
                    [TokenType::AFX_TAG_END(), '>'],
                ]
            ],
            'afx assignment: export const' => [
                'export const Option = <option value={props.value}>{props.label}</option>',
                [
                    [TokenType::MODULE_KEYWORD_EXPORT(), 'export'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_KEYWORD_CONST(), 'const'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'Option'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_ASSIGNMENT(), '='],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'option'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'value'],
                    [TokenType::AFX_ATTRIBUTE_ASSIGNMENT(), '='],
                    [TokenType::AFX_EXPRESSION_START(), '{'],
                    [TokenType::IDENTIFIER(), 'props'],
                    [TokenType::PERIOD(), '.'],
                    [TokenType::IDENTIFIER(), 'value'],
                    [TokenType::AFX_EXPRESSION_END(), '}'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::AFX_EXPRESSION_START(), '{'],
                    [TokenType::IDENTIFIER(), 'props'],
                    [TokenType::PERIOD(), '.'],
                    [TokenType::IDENTIFIER(), 'label'],
                    [TokenType::AFX_EXPRESSION_END(), '}'],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::IDENTIFIER(), 'option'],
                    [TokenType::AFX_TAG_END(), '>'],
                ]
            ],
            'afx assignment: export default' => [
                'export default <div>' . PHP_EOL . 
                '    <h1>Headline</h1>' . PHP_EOL .
                '    <div class={styles.content}>' . PHP_EOL .
                '        Some Content' . PHP_EOL .
                '    </div>' . PHP_EOL .
                '</div>',
                [
                    [TokenType::MODULE_KEYWORD_EXPORT(), 'export'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::MODULE_KEYWORD_DEFAULT(), 'default'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'div'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::END_OF_LINE(), PHP_EOL],
                    [TokenType::WHITESPACE(), '    '],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'h1'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::AFX_TAG_CONTENT(), 'Headline'],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::IDENTIFIER(), 'h1'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::END_OF_LINE(), PHP_EOL],
                    [TokenType::WHITESPACE(), '    '],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::IDENTIFIER(), 'div'],
                    [TokenType::WHITESPACE(), ' '],
                    [TokenType::IDENTIFIER(), 'class'],
                    [TokenType::AFX_ATTRIBUTE_ASSIGNMENT(), '='],
                    [TokenType::AFX_EXPRESSION_START(), '{'],
                    [TokenType::IDENTIFIER(), 'styles'],
                    [TokenType::PERIOD(), '.'],
                    [TokenType::IDENTIFIER(), 'content'],
                    [TokenType::AFX_EXPRESSION_END(), '}'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::END_OF_LINE(), PHP_EOL],
                    [TokenType::WHITESPACE(), '        '],
                    [TokenType::AFX_TAG_CONTENT(), 'Some Content'],
                    [TokenType::END_OF_LINE(), PHP_EOL],
                    [TokenType::AFX_TAG_CONTENT(), '    '],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::IDENTIFIER(), 'div'],
                    [TokenType::AFX_TAG_END(), '>'],
                    [TokenType::END_OF_LINE(), PHP_EOL],
                    [TokenType::AFX_TAG_START(), '<'],
                    [TokenType::AFX_TAG_CLOSE(), '/'],
                    [TokenType::IDENTIFIER(), 'div'],
                    [TokenType::AFX_TAG_END(), '>'],
                ]
            ],
        ];
    }

    /**
     * @dataProvider provider
     * @param string $input
     * @param array<int, array{TokenType, string}> $tokens
     * @return void
     */
    public function test(string $input, array $tokens): void
    {
        $iterator = SourceIterator::createFromSource(Source::createFromString($input));
        $this->assertTokenStream($tokens, Module::tokenize($iterator));
    }
}