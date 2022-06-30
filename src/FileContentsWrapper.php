<?php

namespace App;

class FileContentsWrapper
{
    public function get(string $filename): string
    {
        return file_get_contents($filename);
    }

    public function put(string $filename, mixed $data, int $flags = 0): void
    {
        file_put_contents($filename, $data, $flags);
    }
}