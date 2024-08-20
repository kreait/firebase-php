<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Contract\DynamicLinks;
use Kreait\Firebase\DynamicLink\ShortenLongDynamicLink;
use Kreait\Firebase\Tests\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;

use function mb_strlen;

/**
 * @deprecated 7.14.0 Firebase Dynamic Links is deprecated and should not be used in new projects. The service will
 *                    shut down on August 25, 2025. The component will remain in the SDK until then, but as the
 *                    Firebase service is deprecated, this component is also deprecated
 *
 * @see https://firebase.google.com/support/dynamic-links-faq Dynamic Links Deprecation FAQ
 *
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
        $link = $this->service->createUnguessableLink('https://example.com');

        $this->assertSame($this->domain, $link->domain());
        $this->assertSame(17, mb_strlen($link->suffix()));
    }

    #[Test]
    public function itCreatesAShortLink(): void
    {
        $link = $this->service->createShortLink('https://example.com');

        $this->assertSame($this->domain, $link->domain());
        $this->assertSame(4, mb_strlen($link->suffix()));
    }

    #[Test]
    public function itCreatesAnUnguessableLinkByDefault(): void
    {
        $link = $this->service->createUnguessableLink('https://example.com');

        $this->assertSame($this->domain, $link->domain());
        $this->assertSame(17, mb_strlen($link->suffix()));
    }

    #[Test]
    public function itShortensALongDynamicLinkToAnUnguessableLinkByDefault(): void
    {
        $link = $this->service->shortenLongDynamicLink($this->domain.'/?link=https://example.com');

        $this->assertSame($this->domain, $link->domain());
        $this->assertSame(17, mb_strlen($link->suffix()));
    }

    #[Test]
    public function itShortensALongDynamicLinkToAShortLink(): void
    {
        $link = $this->service->shortenLongDynamicLink(
            $this->domain.'/?link=https://example.com',
            ShortenLongDynamicLink::WITH_SHORT_SUFFIX,
        );

        $this->assertSame($this->domain, $link->domain());
        $this->assertSame(4, mb_strlen($link->suffix()));
    }

    #[Test]
    public function itShortensALongDynamicLinkToAnUnguessableShortLink(): void
    {
        $link = $this->service->shortenLongDynamicLink(
            $this->domain.'/?link=https://example.com',
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
