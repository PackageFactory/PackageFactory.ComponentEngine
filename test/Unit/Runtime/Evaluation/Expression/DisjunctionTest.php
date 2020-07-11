<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Disjunction;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Runtime\Context;
use PackageFactory\ComponentEngine\Runtime\Evaluation\Expression\OnTerm;
use PackageFactory\ComponentEngine\Runtime\Runtime;
use PHPUnit\Framework\TestCase;

final class DisjunctionTest extends TestCase
{
    /**
     * @return \Iterator<string, array{string, Context, mixed}>
     */
    public function averageCaseProvider(): \Iterator
    {
        $input = 'true || true';
        $context = Context::createEmpty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = 'true || false';
        $context = Context::createEmpty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = 'false || true';
        $context = Context::createEmpty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = 'false || false';
        $context = Context::createEmpty();
        $result = false;
        yield $input => [$input, $context, $result];

        $input = 'false || 0';
        $context = Context::createEmpty();
        $result = 0.0;
        yield $input => [$input, $context, $result];

        $input = 'false || 1';
        $context = Context::createEmpty();
        $result = 1.0;
        yield $input => [$input, $context, $result];

        $input = 'false || "Toast"';
        $context = Context::createEmpty();
        $result = "Toast";
        yield $input => [$input, $context, $result];

        $input = 'false || null';
        $context = Context::createEmpty();
        $result = null;
        yield $input => [$input, $context, $result];

        $input = '0 || 0';
        $context = Context::createEmpty();
        $result = 0.0;
        yield $input => [$input, $context, $result];

        $input = '1 || 1';
        $context = Context::createEmpty();
        $result = 1.0;
        yield $input => [$input, $context, $result];

        $input = '"Toast" || "Toast"';
        $context = Context::createEmpty();
        $result = "Toast";
        yield $input => [$input, $context, $result];

        $input = 'null || null';
        $context = Context::createEmpty();
        $result = null;
        yield $input => [$input, $context, $result];

        $input = '0 || true';
        $context = Context::createEmpty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = '1 || true';
        $context = Context::createEmpty();
        $result = 1.0;
        yield $input => [$input, $context, $result];

        $input = '"Toast" || true';
        $context = Context::createEmpty();
        $result = "Toast";
        yield $input => [$input, $context, $result];

        $input = 'null || true';
        $context = Context::createEmpty();
        $result = true;
        yield $input => [$input, $context, $result];

        $input = '0 || false';
        $context = Context::createEmpty();
        $result = false;
        yield $input => [$input, $context, $result];

        $input = '1 || false';
        $context = Context::createEmpty();
        $result = 1.0;
        yield $input => [$input, $context, $result];

        $input = '"Toast" || false';
        $context = Context::createEmpty();
        $result = "Toast";
        yield $input => [$input, $context, $result];

        $input = 'null || false';
        $context = Context::createEmpty();
        $result = false;
        yield $input => [$input, $context, $result];
    }

    /**
     * @test
     * @small
     * @dataProvider averageCaseProvider
     * @param string $input
     * @param Context $context
     * @param mixed $value
     * @return void
     */
    public function testAverageCase(string $input, Context $context, $value): void
    {
        $source = Source::fromString($input);
        $tokenizer = Tokenizer::fromSource($source, Scope\Expression::class);
        $stream = TokenStream::fromTokenizer($tokenizer);
        
        /** @var Disjunction $ast */
        $ast = ExpressionParser::parse($stream);

        $result = OnTerm::evaluate(
            Runtime::default()->withContext($context),
            $ast
        );

        $this->assertSame($value, $result);
    }
}