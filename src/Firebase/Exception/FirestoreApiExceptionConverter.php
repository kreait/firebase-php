<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Exception\Auth\ApiConnectionFailed;
use Kreait\Firebase\Exception\Firestore\FirestoreError;
use Kreait\Firebase\Http\ErrorResponseParser;
use Throwable;

/**
 * @internal
 */
class FirestoreApiExceptionConverter
{
    /** @var ErrorResponseParser */
    private $responseParser;

    /**
     * @internal
     */
    public function __construct()
    {
        $this->responseParser = new ErrorResponseParser();
    }

    public function convertException(Throwable $exception): FirebaseException
    {
        if ($exception instanceof RequestException) {
            return $this->convertGuzzleRequestException($exception);
        }

        if ($exception instanceof ConnectException) {
            return new ApiConnectionFailed('Unable to connect to the API: '.$exception->getMessage(), $exception->getCode(), $exception);
        }

        return new FirestoreError($exception->getMessage(), $exception->getCode(), $exception);
    }

    private function convertGuzzleRequestException(RequestException $e): FirestoreError
    {
        $message = $e->getMessage();
        $code = $e->getCode();

        if ($response = $e->getResponse()) {
            $message = $this->responseParser->getErrorReasonFromResponse($response);
            $code = $response->getStatusCode();
        }

        // TODO: Handle the different errors.

//        if (\mb_stripos($message, 'credentials_mismatch') !== false) {
//            return new CredentialsMismatch('Invalid custom token: The custom token corresponds to a different Firebase project.', $code, $e);
//        }

        return new FirestoreError($message, $code, $e);
    }
}
