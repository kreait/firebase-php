<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use Beste\Json;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
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
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
final class DynamicLinksTest extends TestCase
{
    private MockHandler $httpHandler;
    private string $dynamicLinksDomain = 'https://link.domain.tld';
    private DynamicLinks $service;

    protected function setUp(): void
    {
        $this->httpHandler = new MockHandler();
        $httpClient = new Client(['handler' => HandlerStack::create($this->httpHandler)]);

        $this->service = DynamicLinks::withApiClientAndDefaultDomain($httpClient, $this->dynamicLinksDomain);
    }

    public function testItCreatesADynamicLink(): void
    {
        $this->httpHandler->append(
            new Response(200, [], Json::encode($responseData = [
                'shortLink' => $shortLink = $this->dynamicLinksDomain . '/' . ($suffix = 'short'),
                'previewLink' => $previewLink = $shortLink . '?d=1',
                'warning' => $warnings = [
                    ['warningCode' => 'WARNING_CODE_1', 'warningMessage' => 'Warning Message 1'],
                    ['warningCode' => 'WARNING_CODE_2', 'warningMessage' => 'Warning Message 2'],
                ],
            ])),
        );

        $action = $this->createDynamicLinkAction('https://domain.tld');

        $dynamicLink = $this->service->createDynamicLink($action);

        self::assertTrue($dynamicLink->hasWarnings());
        self::assertCount(2, $dynamicLink->warnings());
        self::assertEquals($warnings, $dynamicLink->warnings());
        self::assertSame($shortLink, (string) $dynamicLink->uri());
        self::assertSame($shortLink, (string) $dynamicLink);
        self::assertSame($previewLink, (string) $dynamicLink->previewUri());
        self::assertSame($this->dynamicLinksDomain, $dynamicLink->domain());
        self::assertSame($suffix, $dynamicLink->suffix());
        self::assertEquals($responseData, Json::decode(Json::encode($dynamicLink), true));
    }

    public function testItCreatesADynamicLinkFromAnArrayOfParameters(): void
    {
        $this->httpHandler->append(
            new Response(200, [], Json::encode($responseData = [
                'shortLink' => $shortLink = $this->dynamicLinksDomain . '/' . ($suffix = 'short'),
                'previewLink' => $previewLink = $shortLink . '?d=1',
            ])),
        );

        $dynamicLink = $this->service->createDynamicLink(['link' => 'https://domain.tld']);

        self::assertFalse($dynamicLink->hasWarnings());
        self::assertCount(0, $dynamicLink->warnings());
        self::assertSame($shortLink, (string) $dynamicLink->uri());
        self::assertSame($shortLink, (string) $dynamicLink);
        self::assertSame($previewLink, (string) $dynamicLink->previewUri());
        self::assertSame($this->dynamicLinksDomain, $dynamicLink->domain());
        self::assertSame($suffix, $dynamicLink->suffix());
        self::assertEquals($responseData, Json::decode(Json::encode($dynamicLink), true));
    }

    public function testCreationFailsIfNoConnectionIsAvailable(): void
    {
        $connectionError = new ConnectException('Connection error', $this->createMock(RequestInterface::class));
        $this->httpHandler->append($connectionError);

        $this->expectException(FailedToCreateDynamicLink::class);
        $this->service->createDynamicLink('https://domain.tld/irrelevant');
    }

    public function testCreationFailsOnUnsuccessfulResponse(): void
    {
        $this->httpHandler->append($response = new Response(400, [], '{}'));

        $action = $this->createDynamicLinkAction('https://domain.tld/irrelevant')
            ->withDynamicLinkDomain('https://page.link.tld') // preventing the action from being changed
        ;

        try {
            $this->service->createDynamicLink($action);
            self::fail('An exception should have been thrown');
        } catch (FailedToCreateDynamicLink $e) {
            self::assertSame($action, $e->action());
            self::assertSame($response, $e->response());
        }
    }

    public function testCreationFailsGracefullyIfAnUnsuccessfulResponseCannotBeParsed(): void
    {
        $this->httpHandler->append(new Response(400, [], 'probably html'));

        $this->expectException(FailedToCreateDynamicLink::class);
        $this->service->createDynamicLink('https://domain.tld/irrelevant');
    }

    public function testItShortensALonkLinkFromAnArrayOfParameters(): void
    {
        $this->httpHandler->append(
            new Response(200, [], Json::encode($responseData = [
                'shortLink' => $shortLink = $this->dynamicLinksDomain . '/' . ($suffix = 'short'),
                'previewLink' => $previewLink = $shortLink . '?d=1',
            ])),
        );

        $dynamicLink = $this->service->shortenLongDynamicLink(['longDynamicLink' => 'https://domain.tld']);

        self::assertFalse($dynamicLink->hasWarnings());
        self::assertCount(0, $dynamicLink->warnings());
        self::assertSame($shortLink, (string) $dynamicLink->uri());
        self::assertSame($shortLink, (string) $dynamicLink);
        self::assertSame($previewLink, (string) $dynamicLink->previewUri());
        self::assertSame($this->dynamicLinksDomain, $dynamicLink->domain());
        self::assertSame($suffix, $dynamicLink->suffix());
        self::assertEquals($responseData, Json::decode(Json::encode($dynamicLink), true));
    }

    public function testShorteningFailsIfNoConnectionIsAvailable(): void
    {
        $connectionError = new ConnectException('Connection error', $this->createMock(RequestInterface::class));
        $this->httpHandler->append($connectionError);

        $this->expectException(FailedToShortenLongDynamicLink::class);
        $this->service->shortenLongDynamicLink('https://domain.tld/irrelevant');
    }

    public function testShorteningFailsOnUnsuccessfulResponse(): void
    {
        $this->httpHandler->append($response = new Response(400, [], '{}'));

        $action = ShortenLongDynamicLink::forLongDynamicLink('https://domain.tld/irrelevant')->withShortSuffix();

        try {
            $this->service->shortenLongDynamicLink($action);
            self::fail('An exception should have been thrown');
        } catch (FailedToShortenLongDynamicLink $e) {
            self::assertJsonStringEqualsJsonString(Json::encode($action), Json::encode($e->action()));
            self::assertSame($response, $e->response());
        }
    }

    public function testShorteningFailsGracefullyIfAnUnsuccessfulResponseCannotBeParsed(): void
    {
        $this->httpHandler->append(new Response(400, [], 'probably html'));

        $this->expectException(FailedToShortenLongDynamicLink::class);
        $this->service->shortenLongDynamicLink('https://domain.tld/irrelevant');
    }

    public function testItGetsLinkStatistics(): void
    {
        $this->httpHandler->append(
            new Response(200, [], Json::encode($responseData = [
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
            ])),
        );

        $stats = $this->service->getStatistics($this->dynamicLinksDomain . '/abcd');
        $eventStats = $stats->eventStatistics();

        self::assertEquals($responseData, $stats->rawData());
        self::assertCount(180, $eventStats);

        self::assertCount(60, $eventStats->clicks());
        self::assertCount(10, $eventStats->clicks()->onAndroid());
        self::assertCount(20, $eventStats->clicks()->onDesktop());
        self::assertCount(30, $eventStats->clicks()->onIOS());
        self::assertCount(10, $eventStats->onAndroid()->clicks());
        self::assertCount(20, $eventStats->onDesktop()->clicks());
        self::assertCount(30, $eventStats->onIOS()->clicks());

        self::assertCount(30, $eventStats->redirects());
        self::assertCount(10, $eventStats->redirects()->onAndroid());
        self::assertCount(0, $eventStats->redirects()->onDesktop());
        self::assertCount(20, $eventStats->redirects()->onIOS());
        self::assertCount(10, $eventStats->onAndroid()->redirects());
        self::assertCount(0, $eventStats->onDesktop()->redirects());
        self::assertCount(20, $eventStats->onIOS()->redirects());

        self::assertCount(30, $eventStats->appInstalls());
        self::assertCount(10, $eventStats->appInstalls()->onAndroid());
        self::assertCount(0, $eventStats->appInstalls()->onDesktop());
        self::assertCount(20, $eventStats->appInstalls()->onIOS());
        self::assertCount(10, $eventStats->onAndroid()->appInstalls());
        self::assertCount(0, $eventStats->onDesktop()->appInstalls());
        self::assertCount(20, $eventStats->onIOS()->appInstalls());

        self::assertCount(30, $eventStats->appFirstOpens());
        self::assertCount(10, $eventStats->appFirstOpens()->onAndroid());
        self::assertCount(0, $eventStats->appFirstOpens()->onDesktop());
        self::assertCount(20, $eventStats->appFirstOpens()->onIOS());
        self::assertCount(10, $eventStats->onAndroid()->appFirstOpens());
        self::assertCount(0, $eventStats->onDesktop()->appFirstOpens());
        self::assertCount(20, $eventStats->onIOS()->appFirstOpens());

        self::assertCount(30, $eventStats->appReOpens());
        self::assertCount(10, $eventStats->appReOpens()->onAndroid());
        self::assertCount(0, $eventStats->appReOpens()->onDesktop());
        self::assertCount(20, $eventStats->appReOpens()->onIOS());
        self::assertCount(10, $eventStats->onAndroid()->appReOpens());
        self::assertCount(0, $eventStats->onDesktop()->appReOpens());
        self::assertCount(20, $eventStats->onIOS()->appReOpens());
    }

    public function testLinkStatsFailIfNoConnectionIsAvailable(): void
    {
        $connectionError = new ConnectException('Connection error', $this->createMock(RequestInterface::class));
        $this->httpHandler->append($connectionError);

        $this->expectException(FailedToGetStatisticsForDynamicLink::class);
        $this->service->getStatistics('anything');
    }

    /**
     * @dataProvider provideCodeAndExpectedMessageRegExForFailingStatisticsRetrieval
     */
    public function testLinkStatsFailOnUnsuccessfulResponse(int $code, string $expectedMessageRegex): void
    {
        $this->httpHandler->append(new Response($code, [], '{"the body does": "not matter here"}'));

        $this->expectException(FailedToGetStatisticsForDynamicLink::class);
        $this->expectExceptionCode($code);
        $this->expectExceptionMessageMatches($expectedMessageRegex);

        $this->service->getStatistics(
            GetStatisticsForDynamicLink::forLink('anything'),
        );
    }

    public function testLinkStatExceptionsProvideTheActionAndTheResponse(): void
    {
        $action = GetStatisticsForDynamicLink::forLink('anything');
        $response = new Response(418, [], '{"key": "value"}');

        $this->httpHandler->append($response);

        try {
            $this->service->getStatistics($action);
            self::fail('An exception should have been thrown');
        } catch (FailedToGetStatisticsForDynamicLink $e) {
            self::assertSame($action, $e->action());
            self::assertSame($response, $e->response());
        }
    }

    public function testDynamicLinkComponentsCanBeCreatedNewOrFromArrays(): void
    {
        self::assertNotEmpty(CreateDynamicLink::new()->jsonSerialize()); // has defaults
        self::assertEmpty(CreateDynamicLink::fromArray([])->jsonSerialize());

        self::assertEmpty(ShortenLongDynamicLink::fromArray([])->jsonSerialize());

        self::assertEmpty(AnalyticsInfo::fromArray([])->jsonSerialize());
        self::assertEmpty(AnalyticsInfo::new()->jsonSerialize());

        self::assertEmpty(GooglePlayAnalytics::fromArray([])->jsonSerialize());
        self::assertEmpty(GooglePlayAnalytics::new()->jsonSerialize());

        self::assertEmpty(ITunesConnectAnalytics::fromArray([])->jsonSerialize());
        self::assertEmpty(ITunesConnectAnalytics::new()->jsonSerialize());

        self::assertEmpty(NavigationInfo::fromArray([])->jsonSerialize());
        self::assertEmpty(NavigationInfo::new()->jsonSerialize());

        self::assertEmpty(IOSInfo::fromArray([])->jsonSerialize());
        self::assertEmpty(IOSInfo::new()->jsonSerialize());

        self::assertEmpty(AndroidInfo::fromArray([])->jsonSerialize());
        self::assertEmpty(AndroidInfo::new()->jsonSerialize());

        self::assertEmpty(SocialMetaTagInfo::fromArray([])->jsonSerialize());
        self::assertEmpty(SocialMetaTagInfo::new()->jsonSerialize());
    }

    /**
     * @return iterable<string, array{0: int, 1: string}>
     */
    public function provideCodeAndExpectedMessageRegExForFailingStatisticsRetrieval(): iterable
    {
        yield '403' => [403, '/missing permissions/i'];

        yield '418' => [418, '/response.+details/'];
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
                            ->withUtmTerm('utmTerm'),
                    )
                    ->withItunesConnectAnalytics(
                        ITunesConnectAnalytics::new()
                            ->withAffiliateToken('affiliateToken')
                            ->withCampaignToken('campaignToken')
                            ->withMediaType('8')
                            ->withProviderToken('providerToken'),
                    ),
            )
            ->withNavigationInfo(
                NavigationInfo::new()
                    ->withForcedRedirect()
                    ->withoutForcedRedirect(), // cheating the code coverage :)
            )
            ->withIOSInfo(
                IOSInfo::new()
                    ->withAppStoreId('appStoreId')
                    ->withBundleId('bundleId')
                    ->withCustomScheme('customScheme')
                    ->withFallbackLink('https://fallback.domain.tld')
                    ->withIPadBundleId('iPadBundleId')
                    ->withIPadFallbackLink('https://ipad-fallback.domain.tld'),
            )
            ->withAndroidInfo(
                AndroidInfo::new()
                    ->withFallbackLink('https://fallback.domain.tld')
                    ->withPackageName('packageName')
                    ->withMinPackageVersionCode('minPackageVersionCode'),
            )
            ->withSocialMetaTagInfo(
                SocialMetaTagInfo::new()
                    ->withDescription('Social Meta Tag description')
                    ->withTitle('Social Meta Tag title')
                    ->withImageLink('https://domain.tld/image.jpg'),
            );
    }
}
