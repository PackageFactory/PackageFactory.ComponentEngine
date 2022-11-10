<?php

namespace PackageFactory\ComponentEngine\Test\Unit\Parser\Tokenizer;

use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Tokenizer;

class TokenizerTest extends \PHPUnit\Framework\TestCase
{
    public function examplesInvalidTagContentsSyntax(): \Iterator
    {
        yield "> inside text" => [
            '<p>acd>def<p>',
            '@TODO: Illegal Character ">"'
        ];

        yield "> between tags" => [
            '<p>><p>',
            '@TODO: Illegal Character ">"'
        ];
        
        /*
         * @todo no exception is thrown on tokenizer level ...
         * 
         * <p>abc<def</p>
         * <p>< </p>
         * <p><</p>
         */
    }
    
    /**
     * @test
     * @dataProvider examplesInvalidTagContentsSyntax
     */
    public function invalidTagContents(string $sourceCode, string $expectedMessage): void
    {
        $tokenizer = Tokenizer::fromSource(
            Source::fromString($sourceCode)
        );
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($expectedMessage);
        
        foreach ($tokenizer as $token) {
            continue;
        }
    }
}
