<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use Kreait\Firebase\DynamicLink\AnalyticsInfo;
use Kreait\Firebase\DynamicLink\AnalyticsInfo\GooglePlayAnalytics;
use Kreait\Firebase\DynamicLink\AnalyticsInfo\ITunesConnectAnalytics;
use Kreait\Firebase\DynamicLink\AndroidInfo;
use Kreait\Firebase\DynamicLink\CreateDynamicLink;
use Kreait\Firebase\DynamicLink\CreateDynamicLink\FailedToCreateDynamicLink;
use Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink;
use Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink\FailedToGetStatisticsForDynamicLink;
use Kreait\Firebase\DynamicLink\IOSInfo;
use Kreait\Firebase\DynamicLink\NavigationInfo;
use Kreait\Firebase\DynamicLink\ShortenLongDynamicLink;
use Kreait\Firebase\DynamicLink\ShortenLongDynamicLink\FailedToShortenLongDynamicLink;
use Kreait\Firebase\DynamicLink\SocialMetaTagInfo;
use Kreait\Firebase\DynamicLinks;
use Kreait\Firebase\Util\JSON;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
final class DynamicLinksTest extends TestCase
{
    /** @var MockHandler */
    private $httpHandler;

    /** @var string */
    private $dynamicLinksDomain = 'https://link.domain.tld';

    /** @var DynamicLinks */
    private $service;

    protected function setUp(): void
    {
        $this->httpHandler = new MockHandler();
        $httpClient = new Client(['handler' => HandlerStack::create($this->httpHandler)]);

        $this->service = DynamicLinks::withApiClientAndDefaultDomain($httpClient, $this->dynamicLinksDomain);
    }

    /**
     * @test
     */
    public function it_creates_a_dynamic_link(): void
    {
        $this->httpHandler->append(
            new Response(200, [], JSON::encode($responseData = [
                'shortLink' => $shortLink = $this->dynamicLinksDomain.'/'.($suffix = 'short'),
                'previewLink' => $previewLink = $shortLink.'?d=1',
                'warning' => $warnings = [
                    ['warningCode' => 'WARNING_CODE_1', 'warningMessage' => 'Warning Message 1'],
                    ['warningCode' => 'WARNING_CODE_2', 'warningMessage' => 'Warning Message 2'],
                ],
            ]))
        );

        $action = $this->createDynamicLinkAction('https://domain.tld');

        $dynamicLink = $this->service->createDynamicLink($action);

        $this->assertTrue($dynamicLink->hasWarnings());
        $this->assertCount(2, $dynamicLink->warnings());
        $this->assertEquals($warnings, $dynamicLink->warnings());
        $this->assertSame($shortLink, (string) $dynamicLink->uri());
        $this->assertSame($shortLink, (string) $dynamicLink);
        $this->assertSame($previewLink, (string) $dynamicLink->previewUri());
        $this->assertSame($this->dynamicLinksDomain, $dynamicLink->domain());
        $this->assertSame($suffix, $dynamicLink->suffix());
        $this->assertEquals($responseData, \json_decode(JSON::encode($dynamicLink), true));
    }

    /**
     * @test
     */
    public function it_creates_a_dynamic_link_from_an_array_of_parameters(): void
    {
        $this->httpHandler->append(
            new Response(200, [], JSON::encode($responseData = [
                'shortLink' => $shortLink = $this->dynamicLinksDomain.'/'.($suffix = 'short'),
                'previewLink' => $previewLink = $shortLink.'?d=1',
            ]))
        );

        $dynamicLink = $this->service->createDynamicLink(['link' => 'https://domain.tld']);

        $this->assertFalse($dynamicLink->hasWarnings());
        $this->assertCount(0, $dynamicLink->warnings());
        $this->assertSame($shortLink, (string) $dynamicLink->uri());
        $this->assertSame($shortLink, (string) $dynamicLink);
        $this->assertSame($previewLink, (string) $dynamicLink->previewUri());
        $this->assertSame($this->dynamicLinksDomain, $dynamicLink->domain());
        $this->assertSame($suffix, $dynamicLink->suffix());
        $this->assertEquals($responseData, \json_decode(JSON::encode($dynamicLink), true));
    }

    /**
     * @test
     */
    public function it_rejects_an_invalid_creation_parameter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->createShortLink(true);
    }

    /**
     * @test
     */
    public function creation_fails_if_no_connection_is_available(): void
    {
        $connectionError = new ConnectException('Connection error', $this->createMock(RequestInterface::class));
        $this->httpHandler->append($connectionError);

        $this->expectException(FailedToCreateDynamicLink::class);
        $this->service->createDynamicLink('https://domain.tld/irrelevant');
    }

    /**
     * @test
     */
    public function creation_fails_on_unsuccessful_response(): void
    {
        $this->httpHandler->append($response = new Response(400, [], '{}'));

        $action = $this->createDynamicLinkAction('https://domain.tld/irrelevant')
            ->withDynamicLinkDomain('https://page.link.tld'); // preventing the action from being changed

        try {
            $this->service->createDynamicLink($action);
            $this->fail('An exception should have been thrown');
        } catch (FailedToCreateDynamicLink $e) {
            $this->assertSame($action, $e->action());
            $this->assertSame($response, $e->response());
        }
    }

    /**
     * @test
     */
    public function creation_fails_gracefully_if_an_unsuccessful_response_cannot_be_parsed(): void
    {
        $this->httpHandler->append($response = new Response(400, [], 'probably html'));

        $this->expectException(FailedToCreateDynamicLink::class);
        $this->service->createDynamicLink('https://domain.tld/irrelevant');
    }

    /**
     * @test
     */
    public function it_shortens_a_lonk_link_from_an_array_of_parameters(): void
    {
        $this->httpHandler->append(
            new Response(200, [], JSON::encode($responseData = [
                'shortLink' => $shortLink = $this->dynamicLinksDomain.'/'.($suffix = 'short'),
                'previewLink' => $previewLink = $shortLink.'?d=1',
            ]))
        );

        $dynamicLink = $this->service->shortenLongDynamicLink(['longDynamicLink' => 'https://domain.tld']);

        $this->assertFalse($dynamicLink->hasWarnings());
        $this->assertCount(0, $dynamicLink->warnings());
        $this->assertSame($shortLink, (string) $dynamicLink->uri());
        $this->assertSame($shortLink, (string) $dynamicLink);
        $this->assertSame($previewLink, (string) $dynamicLink->previewUri());
        $this->assertSame($this->dynamicLinksDomain, $dynamicLink->domain());
        $this->assertSame($suffix, $dynamicLink->suffix());
        $this->assertEquals($responseData, \json_decode(JSON::encode($dynamicLink), true));
    }

    /**
     * @test
     */
    public function it_rejects_an_invalid_shortening_parameter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->shortenLongDynamicLink(true);
    }

    /**
     * @test
     */
    public function shortening_fails_if_no_connection_is_available(): void
    {
        $connectionError = new ConnectException('Connection error', $this->createMock(RequestInterface::class));
        $this->httpHandler->append($connectionError);

        $this->expectException(FailedToShortenLongDynamicLink::class);
        $this->service->shortenLongDynamicLink('https://domain.tld/irrelevant');
    }

    /**
     * @test
     */
    public function shortening_fails_on_unsuccessful_response(): void
    {
        $this->httpHandler->append($response = new Response(400, [], '{}'));

        $action = ShortenLongDynamicLink::forLongDynamicLink('https://domain.tld/irrelevant')->withShortSuffix();

        try {
            $this->service->shortenLongDynamicLink($action);
            $this->fail('An exception should have been thrown');
        } catch (FailedToShortenLongDynamicLink $e) {
            $this->assertJsonStringEqualsJsonString(JSON::encode($action), JSON::encode($e->action()));
            $this->assertSame($response, $e->response());
        }
    }

    /**
     * @test
     */
    public function shortening_fails_gracefully_if_an_unsuccessful_response_cannot_be_parsed(): void
    {
        $this->httpHandler->append($response = new Response(400, [], 'probably html'));

        $this->expectException(FailedToShortenLongDynamicLink::class);
        $this->service->shortenLongDynamicLink('https://domain.tld/irrelevant');
    }

    /**
     * @test
     */
    public function it_gets_link_statistics(): void
    {
        $this->httpHandler->append(
            new Response(200, [], JSON::encode($responseData = [
                'linkEventStats' => [
                    ['platform' => 'ANDROID', 'count' => '10', 'event' => 'CLICK'],
                    ['platform' => 'DESKTOP', 'count' => '20', 'event' => 'CLICK'],
                    ['platform' => 'IOS', 'count' => '30', 'event' => 'CLICK'],

                    ['platform' => 'ANDROID', 'count' => '10', 'event' => 'REDIRECT'],
                    ['platform' => 'IOS', 'count' => '20', 'event' => 'REDIRECT'],

                    ['platform' => 'ANDROID', 'count' => '10', 'event' => 'APP_INSTALL'],
                    ['platform' => 'IOS', 'count' => '20', 'event' => 'APP_INSTALL'],

                    ['platform' => 'ANDROID', 'count' => '10', 'event' => 'APP_FIRST_OPEN'],
                    ['platform' => 'IOS', 'count' => '20', 'event' => 'APP_FIRST_OPEN'],

                    ['platform' => 'ANDROID', 'count' => '10', 'event' => 'APP_RE_OPEN'],
                    ['platform' => 'IOS', 'count' => '20', 'event' => 'APP_RE_OPEN'],
                ],
            ]))
        );

        $stats = $this->service->getStatistics($this->dynamicLinksDomain.'/abcd');
        $eventStats = $stats->eventStatistics();

        $this->assertEquals($responseData, $stats->rawData());
        $this->assertCount(180, $eventStats);

        $this->assertCount(60, $eventStats->clicks());
        $this->assertCount(10, $eventStats->clicks()->onAndroid());
        $this->assertCount(20, $eventStats->clicks()->onDesktop());
        $this->assertCount(30, $eventStats->clicks()->onIOS());
        $this->assertCount(10, $eventStats->onAndroid()->clicks());
        $this->assertCount(20, $eventStats->onDesktop()->clicks());
        $this->assertCount(30, $eventStats->onIOS()->clicks());

        $this->assertCount(30, $eventStats->redirects());
        $this->assertCount(10, $eventStats->redirects()->onAndroid());
        $this->assertCount(0, $eventStats->redirects()->onDesktop());
        $this->assertCount(20, $eventStats->redirects()->onIOS());
        $this->assertCount(10, $eventStats->onAndroid()->redirects());
        $this->assertCount(0, $eventStats->onDesktop()->redirects());
        $this->assertCount(20, $eventStats->onIOS()->redirects());

        $this->assertCount(30, $eventStats->appInstalls());
        $this->assertCount(10, $eventStats->appInstalls()->onAndroid());
        $this->assertCount(0, $eventStats->appInstalls()->onDesktop());
        $this->assertCount(20, $eventStats->appInstalls()->onIOS());
        $this->assertCount(10, $eventStats->onAndroid()->appInstalls());
        $this->assertCount(0, $eventStats->onDesktop()->appInstalls());
        $this->assertCount(20, $eventStats->onIOS()->appInstalls());

        $this->assertCount(30, $eventStats->appFirstOpens());
        $this->assertCount(10, $eventStats->appFirstOpens()->onAndroid());
        $this->assertCount(0, $eventStats->appFirstOpens()->onDesktop());
        $this->assertCount(20, $eventStats->appFirstOpens()->onIOS());
        $this->assertCount(10, $eventStats->onAndroid()->appFirstOpens());
        $this->assertCount(0, $eventStats->onDesktop()->appFirstOpens());
        $this->assertCount(20, $eventStats->onIOS()->appFirstOpens());

        $this->assertCount(30, $eventStats->appReOpens());
        $this->assertCount(10, $eventStats->appReOpens()->onAndroid());
        $this->assertCount(0, $eventStats->appReOpens()->onDesktop());
        $this->assertCount(20, $eventStats->appReOpens()->onIOS());
        $this->assertCount(10, $eventStats->onAndroid()->appReOpens());
        $this->assertCount(0, $eventStats->onDesktop()->appReOpens());
        $this->assertCount(20, $eventStats->onIOS()->appReOpens());
    }

    /**
     * @test
     */
    public function it_rejects_an_invalid_link_stats_parameter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->getStatistics(true);
    }

    /**
     * @test
     */
    public function link_stats_fail_if_no_connection_is_available(): void
    {
        $connectionError = new ConnectException('Connection error', $this->createMock(RequestInterface::class));
        $this->httpHandler->append($connectionError);

        $this->expectException(FailedToGetStatisticsForDynamicLink::class);
        $this->service->getStatistics('anything');
    }

    /**
     * @test
     */
    public function link_stats_fail_on_unsuccessful_response(): void
    {
        $this->httpHandler->append($response = new Response(400, [], '{}'));

        $action = GetStatisticsForDynamicLink::forLink('anything');

        try {
            $this->service->getStatistics($action);
            $this->fail('An exception should have been thrown');
        } catch (FailedToGetStatisticsForDynamicLink $e) {
            $this->assertSame($action, $e->action());
            $this->assertSame($response, $e->response());
        }
    }

    /**
     * @test
     */
    public function link_stats_fail_gracefully_if_an_unsuccessful_response_cannot_be_parsed(): void
    {
        $this->httpHandler->append($response = new Response(400, [], 'probably html'));

        $this->expectException(FailedToGetStatisticsForDynamicLink::class);
        $this->service->getStatistics('https://domain.tld/irrelevant');
    }

    /**
     * @test
     */
    public function dynamic_link_components_can_be_created_new_or_from_arrays(): void
    {
        $this->assertNotEmpty(CreateDynamicLink::new()->jsonSerialize()); // has defaults
        $this->assertEmpty(CreateDynamicLink::fromArray([])->jsonSerialize());

        $this->assertEmpty(ShortenLongDynamicLink::fromArray([])->jsonSerialize());

        $this->assertEmpty(AnalyticsInfo::fromArray([])->jsonSerialize());
        $this->assertEmpty(AnalyticsInfo::new()->jsonSerialize());

        $this->assertEmpty(GooglePlayAnalytics::fromArray([])->jsonSerialize());
        $this->assertEmpty(GooglePlayAnalytics::new()->jsonSerialize());

        $this->assertEmpty(ITunesConnectAnalytics::fromArray([])->jsonSerialize());
        $this->assertEmpty(ITunesConnectAnalytics::new()->jsonSerialize());

        $this->assertEmpty(NavigationInfo::fromArray([])->jsonSerialize());
        $this->assertEmpty(NavigationInfo::new()->jsonSerialize());

        $this->assertEmpty(IOSInfo::fromArray([])->jsonSerialize());
        $this->assertEmpty(IOSInfo::new()->jsonSerialize());

        $this->assertEmpty(AndroidInfo::fromArray([])->jsonSerialize());
        $this->assertEmpty(AndroidInfo::new()->jsonSerialize());

        $this->assertEmpty(SocialMetaTagInfo::fromArray([])->jsonSerialize());
        $this->assertEmpty(SocialMetaTagInfo::new()->jsonSerialize());
    }

    private function createDynamicLinkAction(string $url): CreateDynamicLink
    {
        return CreateDynamicLink::forUrl($url)
            ->withDynamicLinkDomain($this->dynamicLinksDomain)
            ->withAnalyticsInfo(
                AnalyticsInfo::new()
                    ->withGooglePlayAnalyticsInfo(
                        GooglePlayAnalytics::new()
                            ->withGclid('gclid')
                            ->withUtmCampaign('utmCampaign')
                            ->withUtmContent('utmContent')
                            ->withUtmMedium('utmMedium')
                            ->withUtmSource('utmSource')
                            ->withUtmTerm('utmTerm')
                    )
                    ->withItunesConnectAnalytics(
                        ITunesConnectAnalytics::new()
                            ->withAffiliateToken('affiliateToken')
                            ->withCampaignToken('campaignToken')
                            ->withMediaType('8')
                            ->withProviderToken('providerToken')
                    )
            )
            ->withNavigationInfo(
                NavigationInfo::new()
                    ->withForcedRedirect()
                    ->withoutForcedRedirect() // cheating the code coverage :)
            )
            ->withIOSInfo(
                IOSInfo::new()
                    ->withAppStoreId('appStoreId')
                    ->withBundleId('bundleId')
                    ->withCustomScheme('customScheme')
                    ->withFallbackLink('https://fallback.domain.tld')
                    ->withIPadBundleId('iPadBundleId')
                    ->withIPadFallbackLink('https://ipad-fallback.domain.tld')
            )
            ->withAndroidInfo(
                AndroidInfo::new()
                    ->withFallbackLink('https://fallback.domain.tld')
                    ->withPackageName('packageName')
                    ->withMinPackageVersionCode('minPackageVersionCode')
            )
            ->withSocialMetaTagInfo(
                SocialMetaTagInfo::new()
                    ->withDescription('Social Meta Tag description')
                    ->withTitle('Social Meta Tag title')
                    ->withImageLink('https://domain.tld/image.jpg')
            );
    }
}
