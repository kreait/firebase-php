<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink;

use GuzzleHttp\Psr7\Request;
use function GuzzleHttp\Psr7\uri_for;
use Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink;
use Kreait\Firebase\Http\WrappedPsr7Request;
use Psr\Http\Message\RequestInterface;

final class ApiRequest implements RequestInterface
{
    use WrappedPsr7Request;

    public function __construct(GetStatisticsForDynamicLink $action)
    {
        $link = \rawurlencode($action->dynamicLink());

        $uri = uri_for('https://firebasedynamiclinks.googleapis.com/v1/'.$link.'/linkStats?durationDays='.$action->durationInDays());

        $headers = [
            'Content-Type' => 'application/json; charset=UTF-8',
        ];

        $this->wrappedRequest = new Request('GET', $uri, $headers);
    }
}
