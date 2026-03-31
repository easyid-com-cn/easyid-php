<?php

declare(strict_types=1);

namespace EasyID;

final class APIError extends \RuntimeException
{
    public function __construct(
        public readonly int $codeValue,
        string $message,
        public readonly string $requestId
    ) {
        parent::__construct(sprintf('easyid: code=%d message=%s request_id=%s', $codeValue, $message, $requestId));
    }
}
