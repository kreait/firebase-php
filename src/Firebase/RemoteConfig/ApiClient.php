<?php

namespace Kreait\Firebase\RemoteConfig;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Exception\RemoteConfigException;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 */
class ApiClient
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @internal
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function getTemplate(): ResponseInterface
    {
        return $this->request('GET', 'remoteConfig');
    }

    public function validateTemplate(Template $template): ResponseInterface
    {
        return $this->request('PUT', 'remoteConfig', [
            'headers' => [
                'Content-Type' => 'application/json; UTF-8',
                'If-Match' => $template->getEtag(),
            ],
            'query' => [
                'validate_only' => 'true',
            ],
            'body' => JSON::encode($template),
        ]);
    }

    public function publishTemplate(Template $template): ResponseInterface
    {
        return $this->request('PUT', 'remoteConfig', [
            'headers' => [
                'Content-Type' => 'application/json; UTF-8',
                'If-Match' => $template->getEtag(),
            ],
            'body' => JSON::encode($template),
        ]);
    }

    public function listVersions(FindVersions $query, string $nextPageToken = null): ResponseInterface
    {
        $uri = \rtrim((string) $this->client->getConfig('base_uri'), '/').':listVersions';

        $since = $query->since() ? $query->since()->format('Y-m-d\TH:i:s.v\Z') : null;
        $until = $query->until() ? $query->until()->format('Y-m-d\TH:i:s.v\Z') : null;
        $upToVersion = $query->upToVersion() ? (string) $query->upToVersion() : null;

        return $this->request('GET', $uri, \array_filter([
            'startTime' => $since,
            'endTime' => $until,
            'endVersionNumber' => $upToVersion,
            'nextPageToken' => $nextPageToken,
        ]));
    }

    public function rollbackToVersion(VersionNumber $versionNumber): ResponseInterface
    {
        $uri = \rtrim((string) $this->client->getConfig('base_uri'), '/').':rollback';

        return $this->request('POST', $uri, [
            'json' => [
                'version_number' => (string) $versionNumber,
            ],
        ]);
    }

    private function request($method, $uri, array $options = null)
    {
        $options = $options ?? [];

        $options = \array_merge($options, [
            'decode_content' => 'gzip', // sets content-type and deflates response body
        ]);

        try {
            return $this->client->request($method, $uri, $options);
        } catch (RequestException $e) {
            throw RemoteConfigException::fromRequestException($e);
        } catch (\Throwable $e) {
            throw new RemoteConfigException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
