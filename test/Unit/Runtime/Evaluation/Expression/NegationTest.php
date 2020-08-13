<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Negation;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Runtime\Context;
use PackageFactory\ComponentEngine\Runtime\Context\Value\BooleanValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Evaluation\Expression\OnTerm;
use PackageFactory\ComponentEngine\Runtime\Runtime;
use PHPUnit\Framework\TestCase;

final class NegationTest extends TestCase
{
    /**
     * @return \Iterator<string, array{string, ValueInterface, mixed}>
     */
    public function averageCaseProvider(): \Iterator
    {
        $input = '!true';
        $context = Context::createEmpty();
        $result = false;
        yield $input => [$input, $context, $result];

        $input = '!false';
        $context = Context::createEmpty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = '!0';
        $context = Context::createEmpty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = '!12.3';
        $context = Context::createEmpty();
        $result = false;
        yield $input => [$input, $context, $result];

        $input = '!"Hello World"';
        $context = Context::createEmpty();
        $result = false;
        yield $input => [$input, $context, $result];

        $input = '!null';
        $context = Context::createEmpty();
        $result = true;
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
        
        /** @var Negation $ast */
        $ast = ExpressionParser::parse($stream);
        $runtime = Runtime::default()->withContext($context);
        $result = OnTerm::evaluate($runtime, $ast);

        $this->assertInstanceOf(BooleanValue::class, $result);
        $this->assertSame($value, $result->getValue($runtime));
    }
}