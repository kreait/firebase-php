<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Kreait\Firebase\Exception\RemoteConfig\ValidationFailed;
use Kreait\Firebase\Exception\RemoteConfigException;
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
     * Validates the given template without publishing it.
     *
     * @param Template|array $template
     *
     * @throws ValidationFailed if the validation failed
     */
    public function validate($template)
    {
        $template = $template instanceof Template ? $template : Template::fromArray($template);

        $this->client->validateTemplate($template);
    }

    /**
     * @param Template|array $template
     *
     * @throws RemoteConfigException
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
