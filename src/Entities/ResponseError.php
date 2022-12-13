<?php

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
        $this->line = $e->getLine();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->message;
    }
}