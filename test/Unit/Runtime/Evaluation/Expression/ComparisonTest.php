<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Comparison;
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

final class ComparisonTest extends TestCase
{
    /**
     * @return \Iterator<string, array{string, DictionaryValue, mixed}>
     */
    public function averageCaseProvider(): \Iterator
    {
        $input = 'true === true';
        $context = Context::empty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = 'true !== true';
        $context = Context::empty();
        $result = false;
        yield $input => [$input, $context, $result];

        $input = 'false === false';
        $context = Context::empty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = 'false !== false';
        $context = Context::empty();
        $result = false;
        yield $input => [$input, $context, $result];

        $input = 'true === false';
        $context = Context::empty();
        $result = false;
        yield $input => [$input, $context, $result];

        $input = 'true !== false';
        $context = Context::empty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = 'false === true';
        $context = Context::empty();
        $result = false;
        yield $input => [$input, $context, $result];

        $input = 'false !== true';
        $context = Context::empty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = '12.5 === 12.5';
        $context = Context::empty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = '12.5 >= 12.5';
        $context = Context::empty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = '12.5 >= 13';
        $context = Context::empty();
        $result = false;
        yield $input => [$input, $context, $result];

        $input = '12.5 > 12';
        $context = Context::empty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = '12.5 <= 13';
        $context = Context::empty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = '12.5 <= 12.5';
        $context = Context::empty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = '12.5 < 13';
        $context = Context::empty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = '12.5 !== 13';
        $context = Context::empty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = '12.5 !== 12.5';
        $context = Context::empty();
        $result = false;
        yield $input => [$input, $context, $result];

        $input = '"Hello World" === "Hello World"';
        $context = Context::empty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = '"Hello World" !== "Hello World"';
        $context = Context::empty();
        $result = false;
        yield $input => [$input, $context, $result];

        $input = '"Hello World" === "Hello World!"';
        $context = Context::empty();
        $result = false;
        yield $input => [$input, $context, $result];

        $input = '"Hello World" !== "Hello World!"';
        $context = Context::empty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = '"Hello World" <= "Hello World!"';
        $context = Context::empty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = '"Hello World" < "Hello World!"';
        $context = Context::empty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = '"Hello World" > "Hello World!"';
        $context = Context::empty();
        $result = false;
        yield $input => [$input, $context, $result];

        $input = '"Hello World" >= "Hello World!"';
        $context = Context::empty();
        $result = false;
        yield $input => [$input, $context, $result];

        $input = '"aaaa" >= "aaaa"';
        $context = Context::empty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = '"aaaa" >= "bbbb"';
        $context = Context::empty();
        $result = false;
        yield $input => [$input, $context, $result];

        $input = '"aaaa" > "bbbb"';
        $context = Context::empty();
        $result = false;
        yield $input => [$input, $context, $result];

        $input = '"aaaa" < "bbbb"';
        $context = Context::empty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = '"aaaa" <= "bbbb"';
        $context = Context::empty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = '"bbbb" <= "aaaa"';
        $context = Context::empty();
        $result = false;
        yield $input => [$input, $context, $result];

        $input = '"bbbb" < "aaaa"';
        $context = Context::empty();
        $result = false;
        yield $input => [$input, $context, $result];

        $input = '"bbbb" > "aaaa"';
        $context = Context::empty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = '"bbbb" >= "aaaa"';
        $context = Context::empty();
        $result = true;
        yield $input => [$input, $context, $result];
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
        
        /** @var Comparison $ast */
        $ast = ExpressionParser::parse($stream);
        $runtime = Runtime::default()->withContext($context);
        $result = OnTerm::evaluate($runtime, $ast);

        $this->assertInstanceOf(ValueInterface::class, $result);
        $this->assertEquals($value, $result->getValue());
    }
}