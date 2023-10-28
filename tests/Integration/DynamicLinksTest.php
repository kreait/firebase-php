<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Contract\DynamicLinks;
use Kreait\Firebase\DynamicLink\ShortenLongDynamicLink;
use Kreait\Firebase\Tests\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;

use function mb_strlen;

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

    #[Test]
    public function itCreatesAnUnguessableLink(): void
    {
        $link = $this->service->createUnguessableLink('https://domain.example');

        $this->assertSame($this->domain, $link->domain());
        $this->assertSame(17, mb_strlen($link->suffix()));
    }

    #[Test]
    public function itCreatesAShortLink(): void
    {
        $link = $this->service->createShortLink('https://domain.example');

        $this->assertSame($this->domain, $link->domain());
        $this->assertSame(4, mb_strlen($link->suffix()));
    }

    #[Test]
    public function itCreatesAnUnguessableLinkByDefault(): void
    {
        $link = $this->service->createUnguessableLink('https://domain.example');

        $this->assertSame($this->domain, $link->domain());
        $this->assertSame(17, mb_strlen($link->suffix()));
    }

    #[Test]
    public function itShortensALongDynamicLinkToAnUnguessableLinkByDefault(): void
    {
        $link = $this->service->shortenLongDynamicLink($this->domain.'/?link=https://domain.example');

        $this->assertSame($this->domain, $link->domain());
        $this->assertSame(17, mb_strlen($link->suffix()));
    }

    #[Test]
    public function itShortensALongDynamicLinkToAShortLink(): void
    {
        $link = $this->service->shortenLongDynamicLink(
            $this->domain.'/?link=https://domain.example',
            ShortenLongDynamicLink::WITH_SHORT_SUFFIX,
        );

        $this->assertSame($this->domain, $link->domain());
        $this->assertSame(4, mb_strlen($link->suffix()));
    }

    #[Test]
    public function itShortensALongDynamicLinkToAnUnguessableShortLink(): void
    {
        $link = $this->service->shortenLongDynamicLink(
            $this->domain.'/?link=https://domain.example',
            ShortenLongDynamicLink::WITH_UNGUESSABLE_SUFFIX,
        );

        $this->assertSame($this->domain, $link->domain());
        $this->assertSame(17, mb_strlen($link->suffix()));
    }

    #[Test]
    public function itGetsLinkStatistics(): void
    {
        // It always returns at least an empty result. Unfortunately, we don't have "real" dynamic links
        // to test with, but we can at least test that the flow works
        $this->service->getStatistics($this->domain.'/abcd', 13);
        $this->addToAssertionCount(1);
    }
}
