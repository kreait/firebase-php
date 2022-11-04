<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink;
use Kreait\Firebase\Exception\FirebaseException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class FailedToGetStatisticsForDynamicLink extends RuntimeException implements FirebaseException
{
    private ?GetStatisticsForDynamicLink $action = null;
    private ?ResponseInterface $response = null;

    public static function withActionAndResponse(GetStatisticsForDynamicLink $action, ResponseInterface $response): self
    {
        ['code' => $code, 'message' => $message] = self::getCodeAndMessageFromResponse($response);

        $error = new self($message, $code);
        $error->action = $action;
        $error->response = $response;

        return $error;
    }

    public function action(): ?GetStatisticsForDynamicLink
    {
        return $this->action;
    }

    public function response(): ?ResponseInterface
    {
        return $this->response;
    }

    /**
     * @return array{
     *     code: int,
     *     message: string
     * }
     */
    private static function getCodeAndMessageFromResponse(ResponseInterface $response): array
    {
        $message = match ($code = $response->getStatusCode()) {
            StatusCode::STATUS_FORBIDDEN => <<<'MSG'
Firebase reported missing permissions to access the statistics
for the requested Dynamic Link. Please make sure that the
Google Dynamic Links API is enabled for your project at

    https://console.cloud.google.com/apis/library/firebasedynamiclinks.googleapis.com

If the API is enabled, you or the Service Account you're using
might be missing the required permissions. You can check by
visiting

    https://console.cloud.google.com/iam-admin/serviceaccounts/

and making sure that the Service Account has one of the following roles

    - Firebase Admin
    - Firebase Dynamic Links Viewer
    - Firebase Dynamic Links Admin

MSG,
            default => <<<'MSG'
Failed to get statistics for dynamic link. Please inspect the
response for further details.

If the response type is not covered by the SDK, please create
a new issue in the SDK's GitHub repository and include the
HTTP Status code (`$response->getStatusCode()`) and the
message body (`$response->getBody()->getContents()`)

MSG,
        };

        return [
            'code' => $code,
            'message' => $message,
        ];
    }
}
