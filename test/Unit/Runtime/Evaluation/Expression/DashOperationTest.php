<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\DashOperation;
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

final class DashOperationTest extends TestCase
{
    /**
     * @return \Iterator<string, array{string, DictionaryValue, mixed}>
     */
    public function averageCaseProvider(): \Iterator
    {
        $input = '1 + 1';
        $context = Context::empty();
        $result = 2;
        yield $input => [$input, $context, $result];

        $input = '1 + 1 + 1';
        $context = Context::empty();
        $result = 3;
        yield $input => [$input, $context, $result];

        $input = '1 + 0xFF';
        $context = Context::empty();
        $result = 256;
        yield $input => [$input, $context, $result];

        $input = '0xF + 0xF0';
        $context = Context::empty();
        $result = 255;
        yield $input => [$input, $context, $result];

        $input = '0b1 + 0b10';
        $context = Context::empty();
        $result = 3;
        yield $input => [$input, $context, $result];

        $input = '0o1 + 0o10';
        $context = Context::empty();
        $result = 9;
        yield $input => [$input, $context, $result];

        $input = '12.3 + .5';
        $context = Context::empty();
        $result = 12.8;
        yield $input => [$input, $context, $result];

        $input = '1 - 1';
        $context = Context::empty();
        $result = 0;
        yield $input => [$input, $context, $result];

        $input = '1 - 1 - 1';
        $context = Context::empty();
        $result = -1;
        yield $input => [$input, $context, $result];

        $input = '(1 - 1) - 1';
        $context = Context::empty();
        $result = -1;
        yield $input => [$input, $context, $result];

        $input = '1 - (1 - 1)';
        $context = Context::empty();
        $result = 1;
        yield $input => [$input, $context, $result];

        $input = '0xFF - 1';
        $context = Context::empty();
        $result = 254;
        yield $input => [$input, $context, $result];

        $input = '0xF0 - 0xF';
        $context = Context::empty();
        $result = 225;
        yield $input => [$input, $context, $result];

        $input = '0b10 - 0b1';
        $context = Context::empty();
        $result = 1;
        yield $input => [$input, $context, $result];

        $input = '0o10 - 0o1';
        $context = Context::empty();
        $result = 7;
        yield $input => [$input, $context, $result];

        $input = '12.3 - .5';
        $context = Context::empty();
        $result = 11.8;
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
        
        /** @var DashOperation $ast */
        $ast = ExpressionParser::parse($stream);
        $runtime = Runtime::default()->withContext($context);
        $result = OnTerm::evaluate($runtime, $ast);

        $this->assertInstanceOf(ValueInterface::class, $result);
        $this->assertEquals($value, $result->getValue());
    }
}