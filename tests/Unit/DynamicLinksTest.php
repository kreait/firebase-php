<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

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
    /** @var MockHandler */
    private $httpHandler;

    /** @var string */
    private $dynamicLinksDomain = 'https://link.domain.tld';

    /** @var DynamicLinks */
    private $service;

    protected function setUp()
    {
        $this->httpHandler = new MockHandler();
        $httpClient = new Client(['handler' => HandlerStack::create($this->httpHandler)]);

        $this->service = DynamicLinks::withApiClientAndDefaultDomain($httpClient, $this->dynamicLinksDomain);
    }

    /** @test */
    public function it_creates_a_dynamic_link()
    {
        $this->httpHandler->append(
            new Response(200, [], \json_encode($responseData = [
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
        $this->assertEquals($responseData, \json_decode(\json_encode($dynamicLink), true));
    }

    /** @test */
    public function it_creates_a_dynamic_link_from_an_array_of_parameters()
    {
        $this->httpHandler->append(
            new Response(200, [], \json_encode($responseData = [
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
        $this->assertEquals($responseData, \json_decode(\json_encode($dynamicLink), true));
    }

    /** @test */
    public function creation_fails_if_no_connection_is_available()
    {
        $connectionError = ConnectException::create($this->createMock(RequestInterface::class));
        $this->httpHandler->append($connectionError);

        $this->expectException(FailedToCreateDynamicLink::class);
        $this->service->createDynamicLink('https://domain.tld/irrelevant');
    }

    /** @test */
    public function creation_fails_on_unsuccesful_response()
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

    /** @test */
    public function it_shortens_a_lonk_link_from_an_array_of_parameters()
    {
        $this->httpHandler->append(
            new Response(200, [], \json_encode($responseData = [
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
        $this->assertEquals($responseData, \json_decode(\json_encode($dynamicLink), true));
    }

    /** @test */
    public function shortening_fails_if_no_connection_is_available()
    {
        $connectionError = ConnectException::create($this->createMock(RequestInterface::class));
        $this->httpHandler->append($connectionError);

        $this->expectException(FailedToShortenLongDynamicLink::class);
        $this->service->shortenLongDynamicLink('https://domain.tld/irrelevant');
    }

    /** @test */
    public function shortening_fails_on_unsuccesful_response()
    {
        $this->httpHandler->append($response = new Response(400, [], '{}'));

        $action = ShortenLongDynamicLink::forLongDynamicLink('https://domain.tld/irrelevant')->withShortSuffix();

        try {
            $this->service->shortenLongDynamicLink($action);
            $this->fail('An exception should have been thrown');
        } catch (FailedToShortenLongDynamicLink $e) {
            $this->assertJsonStringEqualsJsonString(\json_encode($action), \json_encode($e->action()));
            $this->assertSame($response, $e->response());
        }
    }

    /** @test */
    public function dynamic_link_components_can_be_created_new_or_from_arrays()
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
