<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\PointOperation;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Runtime\Context;
use PackageFactory\ComponentEngine\Runtime\Evaluation\Expression\OnTerm;
use PackageFactory\ComponentEngine\Runtime\Runtime;
use PHPUnit\Framework\TestCase;

final class PointOperationTest extends TestCase
{
    /**
     * @return \Iterator<string, array{string, Context, mixed}>
     */
    public function averageCaseProvider(): \Iterator
    {
        $input = '1 * 1';
        $context = Context::createEmpty();
        $result = 1.0;
        yield $input => [$input, $context, $result];

        $input = '1 * 1 * 1';
        $context = Context::createEmpty();
        $result = 1.0;
        yield $input => [$input, $context, $result];

        $input = '1 * 0';
        $context = Context::createEmpty();
        $result = 0.0;
        yield $input => [$input, $context, $result];

        $input = '0 * 1';
        $context = Context::createEmpty();
        $result = 0.0;
        yield $input => [$input, $context, $result];

        $input = '1 / 1';
        $context = Context::createEmpty();
        $result = 1.0;
        yield $input => [$input, $context, $result];

        $input = '1 / 1 / 1';
        $context = Context::createEmpty();
        $result = 1.0;
        yield $input => [$input, $context, $result];

        $input = '0 / 1';
        $context = Context::createEmpty();
        $result = 0.0;
        yield $input => [$input, $context, $result];

        $input = '1 % 1';
        $context = Context::createEmpty();
        $result = 0.0;
        yield $input => [$input, $context, $result];

        $input = '1 % 1 % 1';
        $context = Context::createEmpty();
        $result = 0.0;
        yield $input => [$input, $context, $result];

        $input = '0 % 1';
        $context = Context::createEmpty();
        $result = 0.0;
        yield $input => [$input, $context, $result];

        $input = '1 * 2';
        $context = Context::createEmpty();
        $result = 2.0;
        yield $input => [$input, $context, $result];

        $input = '2 * 2';
        $context = Context::createEmpty();
        $result = 4.0;
        yield $input => [$input, $context, $result];

        $input = '2 * 2 * 2';
        $context = Context::createEmpty();
        $result = 8.0;
        yield $input => [$input, $context, $result];

        $input = '16 / 2 / 2 / 2 / 2';
        $context = Context::createEmpty();
        $result = 1.0;
        yield $input => [$input, $context, $result];

        $input = '1 / 2';
        $context = Context::createEmpty();
        $result = 0.5;
        yield $input => [$input, $context, $result];

        $input = '1 / 3';
        $context = Context::createEmpty();
        $result = 1 / 3;
        yield $input => [$input, $context, $result];

        $input = '28 / 7';
        $context = Context::createEmpty();
        $result = 4.0;
        yield $input => [$input, $context, $result];

        $input = '7 * 7 / 7';
        $context = Context::createEmpty();
        $result = 7.0;
        yield $input => [$input, $context, $result];

        $input = '1 % 7';
        $context = Context::createEmpty();
        $result = 1.0;
        yield $input => [$input, $context, $result];

        $input = '7 % 7';
        $context = Context::createEmpty();
        $result = 0.0;
        yield $input => [$input, $context, $result];

        $input = '3 % 7 % 2';
        $context = Context::createEmpty();
        $result = 1.0;
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
        
        /** @var PointOperation $ast */
        $ast = ExpressionParser::parse($stream);

        $result = OnTerm::evaluate(
            Runtime::default()->withContext($context),
            $ast
        );

        $this->assertSame($value, $result);
    }
}