<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Test\Util;

use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Count;
use PHPUnit\Framework\Constraint\IsIdentical;

trait TokenizerTestTrait
{
    /**
     * @param array<int, array{TokenType, string}> $expected
     * @param \Iterator<Token> $actual
     * @return void
     */
    public function assertTokenStream(array $expected, \Iterator $actual): void
    {
        $actual = iterator_to_array($actual, false);

        $index = 0;
        foreach ($actual as $token) {
            if (isset($expected[$index])) {
                Assert::assertThat($token->getValue(), new IsIdentical($expected[$index][1]), 'At index ' . $index);
                Assert::assertThat($token->getType(), new IsIdentical($expected[$index][0]), 'At index ' . $index);
            }
            $index++;
        }

        Assert::assertThat($actual, new Count(count($expected)));
    }
}