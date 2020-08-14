<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Ternary;
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

final class TernaryTest extends TestCase
{
    /**
     * @return \Iterator<string, array{string, DictionaryValue, mixed}>
     */
    public function averageCaseProvider(): \Iterator
    {
        $input = 'true ? "yes" : "no"';
        $context = Context::empty();
        $result = "yes";
        yield $input => [$input, $context, $result];

        $input = 'false ? "yes" : "no"';
        $context = Context::empty();
        $result = "no";
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
        
        /** @var Ternary $ast */
        $ast = ExpressionParser::parse($stream);
        $runtime = Runtime::default()->withContext($context);
        $result = OnTerm::evaluate($runtime, $ast);

        $this->assertInstanceOf(ValueInterface::class, $result);
        $this->assertSame($value, $result->getValue());
    }
}