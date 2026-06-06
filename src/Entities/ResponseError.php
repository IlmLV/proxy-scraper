<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Entities;

use Throwable;

final class ResponseError
{
    public string $message;
    public string $file;
    public string $line;

    /**
     * @param Throwable $e
     */
    public function __construct(Throwable $e)
    {
        $this->message = $e->getMessage();
        $this->file = $e->getFile();
        $this->line = (string) $e->getLine();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->message;
    }
}