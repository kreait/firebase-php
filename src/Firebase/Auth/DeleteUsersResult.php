<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\ResponseInterface;

final class DeleteUsersResult
{
    private int $failureCount;
    private int $successCount;

    /**
     * @var array{
     *             index: int,
     *             localId: string,
     *             message: string
     *             }
     */
    private array $rawErrors;

    /**
     * @param array{
     *     index: int,
     *     localId: string,
     *     message: string
     * } $rawErrors
     */
    private function __construct(int $successCount, int $failureCount, array $rawErrors)
    {
        $this->successCount = $successCount;
        $this->failureCount = $failureCount;
        $this->rawErrors = $rawErrors;
    }

    public static function fromRequestAndResponse(DeleteUsersRequest $request, ResponseInterface $response): self
    {
        $data = JSON::decode((string) $response->getBody(), true);
        $errors = $data['errors'] ?? [];

        $failureCount = \count($errors);
        $successCount = \count($request->uids()) - $failureCount;

        return new self($successCount, $failureCount, $errors);
    }

    public function failureCount(): int
    {
        return $this->failureCount;
    }

    public function successCount(): int
    {
        return $this->successCount;
    }

    /**
     * @return array{
     *                index: int,
     *                localId: string,
     *                message: string
     *                }
     */
    public function rawErrors(): array
    {
        return $this->rawErrors;
    }
}
