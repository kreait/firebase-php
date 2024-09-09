<?php

declare(strict_types=1);

namespace Kreait\Firebase\Http;

use Beste\Json;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;

use function is_string;

/**
 * @internal
 */
final class ErrorResponseParser
{
    public function getErrorReasonFromResponse(ResponseInterface $response): string
    {
        $responseBody = (string) $response->getBody();

        try {
            $data = Json::decode($responseBody, true);
        } catch (UnexpectedValueException) {
            return $responseBody;
        }

        if (is_string($data['error']['message'] ?? null)) {
            return $data['error']['message'];
        }

        if (is_string($data['error'] ?? null)) {
            return $data['error'];
        }

        return $responseBody;
    }

    /**
     * @return array<mixed>
     */
    public function getErrorsFromResponse(ResponseInterface $response): array
    {
        try {
            return Json::decode((string) $response->getBody(), true);
        } catch (UnexpectedValueException) {
            return [];
        }
    }
}
