<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Kreait\Firebase\RemoteConfig\ApiClient;
use Kreait\Firebase\RemoteConfig\Template;

/**
 * The Firebase Remote Config.
 *
 * @see https://firebase.google.com/docs/remote-config/use-config-rest
 * @see https://firebase.google.com/docs/remote-config/rest-reference
 */
class RemoteConfig
{
    /**
     * @var ApiClient
     */
    private $client;

    public function __construct(ApiClient $client)
    {
        $this->client = $client;
    }

    public function get(): Template
    {
        return Template::fromResponse($this->client->getTemplate());
    }

    /**
     * @param Template|array $template
     *
     * @return string The etag value of the published template that can be compared to in later calls
     */
    public function publish($template): string
    {
        $template = $template instanceof Template ? $template : Template::fromArray($template);

        $response = $this->client->publishTemplate($template);

        $etag = $response->getHeader('ETag');

        return array_shift($etag);
    }
}
