<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Messaging;

use Kreait\Firebase\Exception\HasRequestAndResponse;
use Kreait\Firebase\Exception\MessagingException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class MessagingError extends RuntimeException implements MessagingException, UnknownError
{
    use HasRequestAndResponse;

    /** @var string[] */
    private $errors = [];

    /**
     * @internal
     *
     * @param string[] $errors
     *
     * @return static
     */
    public function withErrors(array $errors)
    {
        $new = new self($this->getMessage(), $this->getCode(), $this->getPrevious());
        $new->errors = $errors;
        $new->response = $this->response;

        return $new;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * @internal
     *
     * @deprecated 4.28.0
     *
     * @return static
     */
    public function withResponse(ResponseInterface $response)
    {
        $new = new self($this->getMessage(), $this->getCode(), $this->getPrevious());
        $new->errors = $this->errors;
        $new->response = $response;

        return $new;
    }
}
