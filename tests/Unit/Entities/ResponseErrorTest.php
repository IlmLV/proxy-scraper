<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Entities;

use IlmLV\ProxyScraper\Entities\ResponseError;
use PHPUnit\Framework\TestCase;

class ResponseErrorTest extends TestCase
{
    public function testCapturesThrowableDetails(): void
    {
        $line = __LINE__ + 1;
        $exception = new \RuntimeException('boom');

        $error = new ResponseError($exception);

        $this->assertSame('boom', $error->message);
        $this->assertSame(__FILE__, $error->file);
        $this->assertSame((string) $line, $error->line);
        $this->assertSame('boom', (string) $error);
    }
}
