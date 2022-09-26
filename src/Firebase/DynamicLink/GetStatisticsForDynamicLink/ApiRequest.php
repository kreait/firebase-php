<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink;
use Kreait\Firebase\Http\WrappedPsr7Request;
use Psr\Http\Message\RequestInterface;

use function rawurlencode;

final class ApiRequest implements RequestInterface
{
    use WrappedPsr7Request;

    public function __construct(GetStatisticsForDynamicLink $action)
    {
        $link = rawurlencode($action->dynamicLink());

        $uri = Utils::uriFor('https://firebasedynamiclinks.googleapis.com/v1/'.$link.'/linkStats?durationDays='.$action->durationInDays());

        $headers = [
            'Content-Type' => 'application/json; charset=UTF-8',
        ];

        $this->wrappedRequest = new Request('GET', $uri, $headers);
    }
}
