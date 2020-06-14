<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer\Debug;

use PackageFactory\ComponentEngine\Parser\Lexer\Tokenizer;

final class Printer
{
    public static function print(Tokenizer $tokenizer): string
    {
        $lines = [''];
        $lines[] = sprintf(
            "%-30s %-5s %-5s %-5s %-5s %-5s %-5s %s",
            'TYPE',
            'S_IDX',
            'S_ROW',
            'S_COL',
            'E_IDX',
            'E_ROW',
            'E_COL',
            'VALUE'
        );
        foreach ($tokenizer as $token) {
            $lines[] = sprintf(
                "%-30s %-5s %-5s %-5s %-5s %-5s %-5s %s",
                $token->getType(),
                $token->getStart()->getIndex(),
                $token->getStart()->getRowIndex(),
                $token->getStart()->getColumnIndex(),
                $token->getEnd()->getIndex(),
                $token->getEnd()->getRowIndex(),
                $token->getEnd()->getColumnIndex(),
                str_replace("\n", " ", $token->getValue())
            );
        }

        $lines[] = '';
        return implode("\n", $lines);
    }
}