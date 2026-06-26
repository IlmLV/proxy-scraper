<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Entities;

use Throwable;

final class ResponseError
{
    /** Fully-qualified class of the captured throwable. */
    public readonly string $type;
    public readonly string $message;
    public readonly string $file;
    public readonly int $line;

    public function __construct(Throwable $e)
    {
        $this->type = $e::class;
        $this->message = $e->getMessage();
        $this->file = $e->getFile();
        $this->line = $e->getLine();
    }

    public function __toString(): string
    {
        return $this->message;
    }
}
