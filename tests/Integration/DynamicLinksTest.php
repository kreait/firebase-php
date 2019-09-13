<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\DynamicLink\ShortenLongDynamicLink;
use Kreait\Firebase\DynamicLinks;
use Kreait\Firebase\Tests\IntegrationTestCase;

/**
 * @internal
 */
final class DynamicLinksTest extends IntegrationTestCase
{
    /** @var DynamicLinks */
    private $service;

    /** @var string */
    private $domain = 'https://nvpd4.app.goo.gl';

    protected function setUp()
    {
        $this->service = self::$factory->createDynamicLinksService($this->domain);
    }

    /** @test */
    public function it_creates_an_unguessable_link()
    {
        $link = $this->service->createUnguessableLink('https://domain.tld');

        $this->assertSame($this->domain, $link->domain());
        $this->assertSame(17, \mb_strlen($link->suffix()));
    }

    /** @test */
    public function it_creates_a_short_link()
    {
        $link = $this->service->createShortLink('https://domain.tld');

        $this->assertSame($this->domain, $link->domain());
        $this->assertSame(4, \mb_strlen($link->suffix()));
    }

    /** @test */
    public function it_creates_an_unguessable_link_by_default()
    {
        $link = $this->service->createUnguessableLink('https://domain.tld');

        $this->assertSame($this->domain, $link->domain());
        $this->assertSame(17, \mb_strlen($link->suffix()));
    }

    /** @test */
    public function it_shortens_a_long_dynamic_link_to_an_unguessable_link_by_default()
    {
        $link = $this->service->shortenLongDynamicLink($this->domain.'/?link=https://domain.tld');

        $this->assertSame($this->domain, $link->domain());
        $this->assertSame(17, \mb_strlen($link->suffix()));
    }

    /** @test */
    public function it_shortens_a_long_dynamic_link_to_a_short_link()
    {
        $link = $this->service->shortenLongDynamicLink(
            $this->domain.'/?link=https://domain.tld',
            ShortenLongDynamicLink::WITH_SHORT_SUFFIX
        );

        $this->assertSame($this->domain, $link->domain());
        $this->assertSame(4, \mb_strlen($link->suffix()));
    }

    /** @test */
    public function it_shortens_a_long_dynamic_link_to_an_unguessable_short_link()
    {
        $link = $this->service->shortenLongDynamicLink(
            $this->domain.'/?link=https://domain.tld',
            ShortenLongDynamicLink::WITH_UNGUESSABLE_SUFFIX
        );

        $this->assertSame($this->domain, $link->domain());
        $this->assertSame(17, \mb_strlen($link->suffix()));
    }

    /** @test */
    public function it_gets_link_statistics()
    {
        // It always returns at least an empty result. Unfortunately, we don't have "real" dynamic links
        // to test with, but we can at least test that the flow works
        $this->service->getStatistics($this->domain.'/abcd', 13);
        $this->addToAssertionCount(1);
    }
}
