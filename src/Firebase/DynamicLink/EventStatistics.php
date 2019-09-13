<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink;

use Countable;
use IteratorAggregate;

/**
 * @see https://firebase.google.com/docs/reference/dynamic-links/analytics#response_body
 */
final class EventStatistics implements Countable, IteratorAggregate
{
    const PLATFORM_ANDROID = 'ANDROID';
    const PLATFORM_DESKTOP = 'DESKTOP';
    const PLATFORM_IOS = 'IOS';

    // Any click on a Dynamic Link, irrespective to how it is handled and its destinations
    const TYPE_CLICK = 'CLICK';

    // Attempts to redirect users, either to the App Store or Play Store to install or update the app,
    // or to some other destination
    const TYPE_REDIRECT = 'REDIRECT';

    // Actual installs (only supported by the Play Store)
    const TYPE_APP_INSTALL = 'APP_INSTALL';

    // First-opens after an install
    const TYPE_APP_FIRST_OPEN = 'APP_FIRST_OPEN';

    // Re-opens of an app
    const TYPE_APP_RE_OPEN = 'APP_RE_OPEN';

    /** @var array[] */
    private $events;

    private function __construct(array ...$events)
    {
        $this->events = $events;
    }

    public static function fromArray(array $events): self
    {
        return new self(...$events);
    }

    public function onAndroid(): self
    {
        return $this->filterByPlatform(self::PLATFORM_ANDROID);
    }

    public function onDesktop(): self
    {
        return $this->filterByPlatform(self::PLATFORM_DESKTOP);
    }

    public function onIOS(): self
    {
        return $this->filterByPlatform(self::PLATFORM_IOS);
    }

    public function clicks(): self
    {
        return $this->filterByType(self::TYPE_CLICK);
    }

    public function redirects(): self
    {
        return $this->filterByType(self::TYPE_REDIRECT);
    }

    public function appInstalls(): self
    {
        return $this->filterByType(self::TYPE_APP_INSTALL);
    }

    public function appFirstOpens(): self
    {
        return $this->filterByType(self::TYPE_APP_FIRST_OPEN);
    }

    public function appReOpens(): self
    {
        return $this->filterByType(self::TYPE_APP_RE_OPEN);
    }

    public function filterByType(string $type): self
    {
        return $this->filter(static function (array $event) use ($type) {
            return ($event['event'] ?? null) === $type;
        });
    }

    public function filterByPlatform(string $platform): self
    {
        return $this->filter(static function (array $event) use ($platform) {
            return ($event['platform'] ?? null) === $platform;
        });
    }

    public function filter(callable $filter): self
    {
        return new self(...\array_filter($this->events, $filter));
    }

    /**
     * @codeCoverageIgnore
     */
    public function getIterator()
    {
        yield from $this->events;
    }

    public function count()
    {
        return \array_sum(\array_column($this->events, 'count'));
    }
}
