<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Unit\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\ArrayLiteral;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Runtime\Context;
use PackageFactory\ComponentEngine\Runtime\Evaluation\Expression\OnTerm;
use PackageFactory\ComponentEngine\Runtime\Runtime;
use PHPUnit\Framework\TestCase;

final class ArrayTest extends TestCase
{
    /**
     * @return \Iterator<string, array{string, array<mixed>, array<mixed>}>
     */
    public function averageCaseProvider(): \Iterator
    {
        $input = '[]';
        $context = [];
        $value = [];
        yield $input => [$input, $context, $value];

        $input = '[null]';
        $context = [];
        $value = [null];
        yield $input => [$input, $context, $value];

        $input = '[true]';
        $context = [];
        $value = [true];
        yield $input => [$input, $context, $value];

        $input = '[1]';
        $context = [];
        $value = [1];
        yield $input => [$input, $context, $value];

        $input = '["Hello World!"]';
        $context = [];
        $value = ["Hello World!"];
        yield $input => [$input, $context, $value];

        $input = '[1, 2, 3]';
        $context = [];
        $value = [1, 2, 3];
        yield $input => [$input, $context, $value];

        $input = '[[[[[[1, 2, 3]]]]]]';
        $context = [];
        $value = [[[[[[1, 2, 3]]]]]];
        yield $input => [$input, $context, $value];

        $input = '[[], [[[], [[[1, 2, 3], ["Foo"]]]]]]';
        $context = [];
        $value = [[], [[[], [[[1, 2, 3], ["Foo"]]]]]];
        yield $input => [$input, $context, $value];

        $input = '[value]';
        $context = ['value' => 12.3];
        $value = [12.3];
        yield $input => [$input, $context, $value];

        $input = '[...value]';
        $context = ['value' => [12.3, true, "Yep!"]];
        $value = [12.3, true, "Yep!"];
        yield $input => [$input, $context, $value];

        $input = '[...value, ...value, ...value]';
        $context = ['value' => [12.3, true, "Yep!"]];
        $value = [12.3, true, "Yep!", 12.3, true, "Yep!", 12.3, true, "Yep!"];
        yield $input => [$input, $context, $value];

        $input = '[11, ...[12, 13, true, false]]';
        $context = [];
        $value = [11, 12, 13, true, false];
        yield $input => [$input, $context, $value];

        $input = '[...[...[...[...value]]]]';
        $context = ['value' => [12.3, true, "Yep!"]];
        $value = [12.3, true, "Yep!"];
        yield $input => [$input, $context, $value];
    }

    /**
     * @test
     * @small
     * @dataProvider averageCaseProvider
     * @param string $input
     * @param array<mixed> $context
     * @param array<mixed> $value
     * @return void
     */
    public function testAverageCase(string $input, array $context, array $value): void
    {
        $source = Source::fromString($input);
        $tokenizer = Tokenizer::fromSource($source, Scope\Expression::class);
        $stream = TokenStream::fromTokenizer($tokenizer);
        
        /** @var ArrayLiteral $ast */
        $ast = ExpressionParser::parse($stream);

        $result = OnTerm::evaluate(
            Runtime::default()->withContext(Context::fromArray($context)),
            $ast
        );

        $this->assertEquals($value, $result);
    }

    /**
     * @return \Iterator<string, array{string, array<mixed>}>
     */
    public function exceptionCaseProvider(): \Iterator
    {
        $input = '[...value]';
        $context = ['value' => ['foo' => 'bar']];
        yield $input => [$input, $context];
    }

        /**
     * @test
     * @small
     * @dataProvider exceptionCaseProvider
     * @param string $input
     * @param array<mixed> $context
     * @return void
     */
    public function testExceptionCase(string $input, array $context): void
    {
        $this->expectException(\Exception::class);

        $source = Source::fromString($input);
        $tokenizer = Tokenizer::fromSource($source, Scope\Expression::class);
        $stream = TokenStream::fromTokenizer($tokenizer);
        
        /** @var ArrayLiteral $ast */
        $ast = ExpressionParser::parse($stream);

        $result = OnTerm::evaluate(
            Runtime::default()->withContext(Context::fromArray($context)),
            $ast
        );
    }
}