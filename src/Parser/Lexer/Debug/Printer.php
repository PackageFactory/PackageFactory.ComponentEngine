<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer\Debug;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;

final class Printer
{
    /**
     * @param \Iterator<Token> $tokenStream
     * @return void
     */
    public static function print(\Iterator $tokenStream): void
    {
        $lines = [''];
        echo sprintf(
            "%-40s %-5s %-5s %-5s %-5s %-5s %-5s %s",
            'TYPE',
            'S_IDX',
            'S_ROW',
            'S_COL',
            'E_IDX',
            'E_ROW',
            'E_COL',
            'VALUE'
        ) . PHP_EOL;
        foreach ($tokenStream as $token) {
            echo sprintf(
                "%-40s %-5s %-5s %-5s %-5s %-5s %-5s %s",
                $token->getType(),
                $token->getStart()->getIndex(),
                $token->getStart()->getRowIndex(),
                $token->getStart()->getColumnIndex(),
                $token->getEnd()->getIndex(),
                $token->getEnd()->getRowIndex(),
                $token->getEnd()->getColumnIndex(),
                str_replace("\n", " ", $token->getValue())
            ) . PHP_EOL;
        }

        // $lines[] = '';
        // return implode("\n", $lines);
    }
}