<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\TemplateLiteral;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Runtime\Context;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Evaluation\Expression\OnTerm;
use PackageFactory\ComponentEngine\Runtime\Runtime;
use PHPUnit\Framework\TestCase;

final class TemplateLiteralTest extends TestCase
{
    /**
     * @return \Iterator<string, array{string, ValueInterface, mixed}>
     */
    public function averageCaseProvider(): \Iterator
    {
        $input = '`Hello, ${name}!`';
        $context = Context::fromArray(['name' => 'Jane Doe']);
        $result = "Hello, Jane Doe!";
        yield $input => [$input, $context, $result];

        $input = '`Point: ${x}, ${y}`';
        $context = Context::fromArray(['x' => 12.0, 'y' => 25.3]);
        $result = "Point: 12, 25.3";
        yield $input => [$input, $context, $result];

        $input = '`List: ${list}`';
        $context = Context::fromArray(['list' => [1, 2, 3, 4]]);
        $result = "List: 1,2,3,4";
        yield $input => [$input, $context, $result];

        $input = '`Nested List: ${list}`';
        $context = Context::fromArray(['list' => [1, [2, 3], 4]]);
        $result = "Nested List: 1,2,3,4";
        yield $input => [$input, $context, $result];

        $input = '`Boolean (true): ${boolean}`';
        $context = Context::fromArray(['boolean' => true]);
        $result = "Boolean (true): true";
        yield $input => [$input, $context, $result];

        $input = '`Boolean (false): ${boolean}`';
        $context = Context::fromArray(['boolean' => false]);
        $result = "Boolean (false): false";
        yield $input => [$input, $context, $result];

        $input = '`Null: ${null}`';
        $context = Context::createEmpty();
        $result = "Null: null";
        yield $input => [$input, $context, $result];
    }

    /**
     * @test
     * @small
     * @dataProvider averageCaseProvider
     * @param string $input
     * @param ValueInterface $context
     * @param mixed $value
     * @return void
     */
    public function testAverageCase(string $input, ValueInterface $context, $value): void
    {
        $source = Source::fromString($input);
        $tokenizer = Tokenizer::fromSource($source, Scope\Expression::class);
        $stream = TokenStream::fromTokenizer($tokenizer);
        
        /** @var TemplateLiteral $ast */
        $ast = ExpressionParser::parse($stream);
        $runtime = Runtime::default()->withContext($context);
        $result = OnTerm::evaluate($runtime, $ast);

        $this->assertInstanceOf(ValueInterface::class, $result);
        $this->assertSame($value, $result->getValue($runtime));
    }
}