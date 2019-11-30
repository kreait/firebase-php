<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Util;

use JsonSerializable;

final class AuthError implements JsonSerializable
{
    /** @var string */
    private $message;

    /** @var int */
    private $code;

    /** @var string */
    private $reason;

    /** @var string */
    private $domain;

    public function __construct(string $message, int $code = null, string $reason = null, string $domain = null)
    {
        $this->message = $message;
        $this->code = $code ?? 400;
        $this->reason = $reason ?? 'invalid';
        $this->domain = $domain ?? 'global';
    }

    public function jsonSerialize()
    {
        return [
            'error' => [
                'errors' => [
                    'domain' => $this->domain,
                    'reason' => $this->reason,
                    'message' => $this->message,
                ],
            ],
            'code' => $this->code,
            'message' => $this->message,
        ];
    }
}
