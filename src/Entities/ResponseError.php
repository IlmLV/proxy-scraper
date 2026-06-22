<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Entities;

use Throwable;

final class ResponseError
{
    public string $message;
    public string $file;
    public int $line;

    public function __construct(Throwable $e)
    {
        $this->message = $e->getMessage();
        $this->file = $e->getFile();
        $this->line = $e->getLine();
    }

    public function __toString(): string
    {
        return $this->message;
    }
}
