<?php

namespace App\Twig;

class TwigStrPad
{
    public static function execute(
        string $string,
        int $length,
        string $pad_string = " ",
        int $pad_type = STR_PAD_RIGHT
    ): string
    {
        return str_pad($string, $length, $pad_string, $pad_type);
    }
}