<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Contract\DynamicLinks;
use Kreait\Firebase\DynamicLink\ShortenLongDynamicLink;
use Kreait\Firebase\Tests\IntegrationTestCase;

/**
 * @internal
 */
final class DynamicLinksTest extends IntegrationTestCase
{
    private DynamicLinks $service;

    private string $domain = 'https://beste.page.link';

    protected function setUp(): void
    {
        $this->service = self::$factory->createDynamicLinksService($this->domain);
    }

    public function testItCreatesAnUnguessableLink(): void
    {
        $link = $this->service->createUnguessableLink('https://domain.tld');

        $this->assertSame($this->domain, $link->domain());
        $this->assertSame(17, \mb_strlen($link->suffix()));
    }

    public function testItCreatesAShortLink(): void
    {
        $link = $this->service->createShortLink('https://domain.tld');

        $this->assertSame($this->domain, $link->domain());
        $this->assertSame(4, \mb_strlen($link->suffix()));
    }

    public function testItCreatesAnUnguessableLinkByDefault(): void
    {
        $link = $this->service->createUnguessableLink('https://domain.tld');

        $this->assertSame($this->domain, $link->domain());
        $this->assertSame(17, \mb_strlen($link->suffix()));
    }

    public function testItShortensALongDynamicLinkToAnUnguessableLinkByDefault(): void
    {
        $link = $this->service->shortenLongDynamicLink($this->domain.'/?link=https://domain.tld');

        $this->assertSame($this->domain, $link->domain());
        $this->assertSame(17, \mb_strlen($link->suffix()));
    }

    public function testItShortensALongDynamicLinkToAShortLink(): void
    {
        $link = $this->service->shortenLongDynamicLink(
            $this->domain.'/?link=https://domain.tld',
            ShortenLongDynamicLink::WITH_SHORT_SUFFIX
        );

        $this->assertSame($this->domain, $link->domain());
        $this->assertSame(4, \mb_strlen($link->suffix()));
    }

    public function testItShortensALongDynamicLinkToAnUnguessableShortLink(): void
    {
        $link = $this->service->shortenLongDynamicLink(
            $this->domain.'/?link=https://domain.tld',
            ShortenLongDynamicLink::WITH_UNGUESSABLE_SUFFIX
        );

        $this->assertSame($this->domain, $link->domain());
        $this->assertSame(17, \mb_strlen($link->suffix()));
    }

    public function testItGetsLinkStatistics(): void
    {
        // It always returns at least an empty result. Unfortunately, we don't have "real" dynamic links
        // to test with, but we can at least test that the flow works
        $this->service->getStatistics($this->domain.'/abcd', 13);
        $this->addToAssertionCount(1);
    }
}
