<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer\Debug;

use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;

final class Printer
{
    public static function print(iterable $tokenStream): void
    {
        $lines = [''];
        $lines[] = sprintf(
            "%-40s %-5s %-5s %-5s %-5s %-5s %-5s %s",
            'TYPE',
            'S_IDX',
            'S_ROW',
            'S_COL',
            'E_IDX',
            'E_ROW',
            'E_COL',
            'VALUE'
        );
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