<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Chain;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Runtime\Context;
use PackageFactory\ComponentEngine\Runtime\Context\Value\DictionaryValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Evaluation\Expression\OnTerm;
use PackageFactory\ComponentEngine\Runtime\Runtime;
use PHPUnit\Framework\TestCase;

final class ChainTest extends TestCase
{
    /**
     * @return \Iterator<string, array{string, DictionaryValue, mixed}>
     */
    public function averageCaseProvider(): \Iterator
    {
        $values = [
            'number' => [42, 42], 
            'number with decimal point' => [12.3, 12.3], 
            'boolean (true)' => [true, true], 
            'boolean (false)' => [false, false],
            'null' => [null, null], 
            'string' => ["Hello Chain!", "Hello Chain!"],
            'array of numbers' => [[12, 13, 14], [12, 13, 14]],
            'array of strings' => [["foo", "bar", "baz"], ["foo", "bar", "baz"]],
            'associative array of numbers' => [
                [
                    'twelve' => 12,
                    'thirteen' => 13,
                    'fourteen' => 14,
                ],
                (object) [
                    'twelve' => 12,
                    'thirteen' => 13,
                    'fourteen' => 14,
                ]
            ],
            'associative array of strings' => [
                [
                    'foo' => "Hello foo!",
                    'bar' => "Hello bar!",
                    'baz' => "Hello baz!"
                ],
                (object) [
                    'foo' => "Hello foo!",
                    'bar' => "Hello bar!",
                    'baz' => "Hello baz!"
                ]
            ],
            'class' => [
                $newClass =  new class {
                    /**
                     * @var int
                     */
                    public $foo = 12;
    
                    /**
                     * @var string
                     */
                    public $bar = 'Hello Class!';
    
                    /**
                     * @var int
                     */
                    public $baz = 25;
                },
                $newClass
            ]
        ];

        foreach ($values as $description => $value) {
            $input = 'value';
            $context = Context::fromArray(['value' => $value[0]]);
            $result = $value[1];
            yield "$input = $description" => 
                [$input, $context, $result];
    
            $input = 'foo.bar';
            $context = Context::fromArray(['foo' => ['bar' => $value[0]]]);
            $result = $value[1];
            yield "$input = $description" => 
                [$input, $context, $result];
    
            $input = 'foo.bar.baz';
            $context = Context::fromArray(['foo' => ['bar' => ['baz' => $value[0]]]]);
            $result = $value[1];
            yield "$input = $description" => 
                [$input, $context, $result];

            $input = 'foo[2]';
            $context = Context::fromArray(['foo' => [null, null, $value[0], null, null]]);
            $result = $value[1];
            yield "$input = $description" => 
                [$input, $context, $result];

            $input = 'foo.bar[2]';
            $context = Context::fromArray(['foo' => ['bar' => [null, null, $value[0], null, null]]]);
            $result = $value[1];
            yield "$input = $description" => 
                [$input, $context, $result];

            $input = 'foo.property.baz';
            $context = Context::fromArray(['foo' => new class($value[0]) {
                /**
                 * @var array<mixed>
                 */
                public $property;

                /**
                 * @param mixed $value
                 */
                public function __construct($value) 
                {
                    $this->property = ['baz' => $value];
                }
            }]);
            $result = $value[1];
            yield "$input = $description" => 
                [$input, $context, $result];

            $input = 'foo.gettableProperty.baz';
            $context = Context::fromArray(['foo' => new class($value[0]) {
                /**
                 * @var array<mixed>
                 */
                private $property;

                /**
                 * @param mixed $value
                 */
                public function __construct($value) 
                {
                    $this->property = ['baz' => $value];
                }

                /**
                 * @return array<mixed>
                 */
                public function getGettableProperty(): array
                {
                    return $this->property;
                }
            }]);
            $result = $value[1];
            yield "$input = $description" => 
                [$input, $context, $result];
        }

        $input = '{foo: 12}.foo';
        $result = 12;
        yield "$input = $result" => [$input, Context::empty(), $result];

        $input = '{foo: {bar: 13}}.foo.bar';
        $result = 13;
        yield "$input = $result" => [$input, Context::empty(), $result];

        $input = '{foo: {bar: {baz: 14}}}.foo.bar.baz';
        $result = 14;
        yield "$input = $result" => [$input, Context::empty(), $result];

        $input = '{foo: "hello"}.foo';
        $result = "hello";
        yield "$input = $result" => [$input, Context::empty(), $result];

        $input = '{foo: {bar: "goodbye!"}}.foo.bar';
        $result = "goodbye!";
        yield "$input = $result" => [$input, Context::empty(), $result];

        $input = '{foo: {bar: {baz: "hello again!"}}}.foo.bar.baz';
        $result = "hello again!";
        yield "$input = $result" => [$input, Context::empty(), $result];

        $input = '[11, 12, 13][0]';
        $result = 11;
        yield "$input = $result" => [$input, Context::empty(), $result];

        $input = '[11, 12, 13][1]';
        $result = 12;
        yield "$input = $result" => [$input, Context::empty(), $result];

        $input = '[11, 12, 13][2]';
        $result = 13;
        yield "$input = $result" => [$input, Context::empty(), $result];

        $input = '[11, [[12, ["Hello World!"]]], 13][1][0][1][0]';
        $result = "Hello World!";
        yield "$input = $result" => [$input, Context::empty(), $result];

        $input = '{foo: [1, { bar: [0, 23] }]}.foo[1].bar[1]';
        $result = 23;
        yield "$input = $result" => [$input, Context::empty(), $result];

        $input = '"Hello World"[2]';
        $result = 'l';
        yield "$input = $result" => [$input, Context::empty(), $result];

    }

    /**
     * @test
     * @small
     * @dataProvider averageCaseProvider
     * @param string $input
     * @param DictionaryValue $context
     * @param mixed $value
     * @return void
     */
    public function testAverageCase(string $input, DictionaryValue $context, $value): void
    {
        $source = Source::fromString($input);
        $tokenizer = Tokenizer::fromSource($source, Scope\Expression::class);
        $stream = TokenStream::fromTokenizer($tokenizer);
        
        /** @var Chain $ast */
        $ast = ExpressionParser::parse($stream);
        $runtime = Runtime::default()->withContext($context);
        $result = OnTerm::evaluate($runtime, $ast);

        $this->assertInstanceOf(ValueInterface::class, $result);
        $this->assertEquals($value, $result->getValue());
    }
}