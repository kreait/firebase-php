#############
Dynamic Links
#############

You can create short Dynamic Links with the Firebase Admin SDK for PHP. Dynamic Links can be

- a long Dynamic Link
- an array containing Dynamic Link parameters
- an action created with builder methods

and will return a URL like ``https://example.page.link/wXYZ``.


.. note::
    Short Dynamic Links created via the REST API or this SDK do not show up in the Firebase console. Such Dynamic Links
    are intended for user-to-user sharing. For marketing use cases, continue to create your links directly through the
    `Dynamic Links page <https://console.firebase.google.com/project/_/durablelinks/>`_ of the Firebase console.

Before you start, please read about Dynamic Links in the official documentation:

- `Dynamic Links Product Page <https://firebase.google.com/products/dynamic-links/>`_
- `Create Dynamic Links with the REST API <https://firebase.google.com/docs/dynamic-links/rest>`_
- `Long Dynamic Links <https://firebase.google.com/docs/dynamic-links/create-manually>`_
- `Dynamic Link API Reference <https://firebase.google.com/docs/reference/dynamic-links/link-shortener>`_

***************
Getting started
***************

- In the Firebase console, open the
  `Dynamic Links <https://console.firebase.google.com/u/1/project/_/durablelinks/links/>`_ section.
- If you have not already accepted the terms of service and set a domain for your Dynamic Links, do so when prompted.
- If you already have a Dynamic Links domain, take note of it. You need to provide a Dynamic Links Domain when you
  programmatically create Dynamic Links.

****************************************
Initializing the Dynamic Links component
****************************************

**With the SDK**

.. code-block:: php

    $dynamicLinksDomain = 'https://example.page.link';
    $dynamicLinks = $factory->createDynamicLinksService($dynamicLinksDomain);

**With Dependency Injection** (`Symfony Bundle <https://github.com/kreait/firebase-bundle>`_/`Laravel/Lumen Package <https://github.com/kreait/laravel-firebase>`_)

To define the default Dynamic Links Domain for **Laravel**, configure the ``FIREBASE_DYNAMIC_LINKS_DEFAULT_DOMAIN`` environment variable.

.. code-block:: php

    use Kreait\Firebase\Contract\DynamicLinks;

    class MyService
    {
        public function __construct(DynamicLinks $dynamicLinks)
        {
            $this->dynamicLinks = $dynamicLinks;
        }
    }

**With the Laravel** ``app()`` **helper** (`Laravel/Lumen Package <https://github.com/kreait/laravel-firebase>`_)

To define the default Dynamic Links Domain, configure the ``FIREBASE_DYNAMIC_LINKS_DEFAULT_DOMAIN`` environment variable.

.. code-block:: php

    $dynamicLinks = app('firebase.dynamic_links');


*********************
Create a Dynamic Link
*********************

You can create a Dynamic Link by using one of the methods below. Each method will return an instance of
``Kreait\Firebase\DynamicLink``.

.. code-block:: php

    use use Kreait\Firebase\DynamicLink\CreateDynamicLink\FailedToCreateDynamicLink;

    $url = 'https://www.example.com/some/path';

    try {
        $link = $dynamicLinks->createUnguessableLink($url);
        $link = $dynamicLinks->createDynamicLink($url, CreateDynamicLink::WITH_UNGUESSABLE_SUFFIX);

        $link = $dynamicLinks->createShortLink($url);
        $link = $dynamicLinks->createDynamicLink($url, CreateDynamicLink::WITH_SHORT_SUFFIX);
    } catch (FailedToCreateDynamicLink $e) {
        echo $e->getMessage(); exit;
    }

If ``createDynamicLink()`` is called without a second parameter, the Dynamic Link is created with an unguessable suffix.

Unguessable suffixes have a length of 17 characters, short suffixes a length of 4 characters. You can learn more about
the length of Dynamic Links in the
`official documentation <https://firebase.google.com/docs/dynamic-links/rest#set_the_length_of_a_short>`_.

The returned object will be an instance of ``Kreait\Firebase\DynamicLink`` with the following accessors:

.. code-block:: php

    $link->uri();         // Psr\Http\Message\UriInterface
    $link->previewUri();  // Psr\Http\Message\UriInterface
    $link->domain();      // string
    $link->suffix();      // string
    $link->hasWarnings(); // bool
    $link->warnings();    // array

    $uriString = (string) $link;

************************************
Create a short link from a long link
************************************

If you have a `manually constructed link <https://firebase.google.com/docs/dynamic-links/create-manually>`_,
you can convert it to a short link:

.. code-block:: php

    use Kreait\Firebase\DynamicLink\ShortenLongDynamicLink\FailedToShortenLongDynamicLink;

    $longLink = 'https://example.page.link?link=https://domain.tld/some/path';

    try {
        $link = $dynamicLinks->shortenLongDynamicLink($longLink);
        $link = $dynamicLinks->shortenLongDynamicLink($longLink, ShortenLongDynamicLink::WITH_UNGUESSABLE_SUFFIX);
        $link = $dynamicLinks->shortenLongDynamicLink($longLink, ShortenLongDynamicLink::WITH_SHORT_SUFFIX);
    } catch (FailedToShortenLongDynamicLink $e) {
        echo $e->getMessage(); exit;
    }

If ``shortenLongDynamicLink()`` is called without a second parameter, the Dynamic Link is created with an unguessable suffix.

*******************
Get link statistics
*******************

You can use this REST API to get analytics data for each of your short Dynamic Links, whether created in the console
or programmatically.

.. note::
    These statistics might not include events that have been logged within the last 36 hours.

.. code-block:: php

    use Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink\FailedToGetStatisticsForDynamicLink;

    try {
        $stats = $dynamicLinks->getStatistics('https://example.page.link/wXYZ');
        $stats = $dynamicLinks->getStatistics('https://example.page.link/wXYZ', 14); // duration in days
    } catch (FailedToGetStatisticsForDynamicLink $e) {
        echo $e->getMessage(); exit;
    }

If ``getStatistics()`` is called without a second parameter, stats will include the statistics of the past 7 days.

The returned object will be an instance of ``Kreait\Firebase\DynamicLink\DynamicLinkStatistics``, which currently
only includes event statistics. You can access the raw returned data with `$stats->rawData()`.

Event Statistics
----------------

Firebase Dynamic Links tracks the number of times each of your short Dynamic Links have been clicked, as well as the
number of times a click resulted in a redirect, app install, app first-open, or app re-open, including the platform
on which that event occurred.

Each of the following methods returns a (filtered) instance of ``Kreait\Firebase\DynamicLink\EventStatistics`` which
supports any combination of filters and is countable with ``count()`` or ``->count()`` as shown below:

.. code-block:: php

    $eventStats = $stats->eventStatistics();

    $allClicks = $eventStats->clicks();
    $allRedirects = $eventStats->redirects();
    $allAppInstalls = $eventStats->appInstalls();
    $allAppFirstOpens = $eventStats->appFirstOpens();
    $allAppReOpens = $eventStats->appReOpens();

    $allAndroidEvents = $eventStats->onAndroid();
    $allDesktopEvents = $eventStats->onDesktop();
    $allIOSEvents = $eventStats->onIOS();

    $clicksOnDesktop = $eventStats->clicks()->onDesktop();
    $appInstallsOnAndroid = $eventStats->onAndroid()->appInstalls();
    $appReOpensOnIOS = $eventStats->appReOpens()->onIOS();

    $totalAmountOfClicks = count($eventStats->clicks());
    $totalAmountOfAppFirstOpensOnAndroid = $eventStats->appFirstOpens()->onAndroid()->count();

    $custom = $eventStats->filter(function (array $eventGroup) {
        return $eventGroup['platform'] === 'CUSTOM_PLATFORM_THAT_THE_SDK_DOES_NOT_KNOW_YET';
    });

**************
Advanced usage
**************

Using actions
-------------

You can fully customize the creation of Dynamic Links by building up a ``Kreait\Firebase\DynamicLink\CreateDynamicLink``
action. The following code shows all available building components:

.. code-block:: php

    use Kreait\Firebase\DynamicLink\CreateDynamicLink;

    $action = CreateDynamicLink::forUrl($url)
        ->withDynamicLinkDomain('https://example.page.link')
        ->withUnguessableSuffix() // default
        // or
        ->withShortSuffix()
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
                ->withoutForcedRedirect() // default
                // or
                ->withForcedRedirect()
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

    $link = $dynamicLinks->createDynamicLink($action);

Using parameter arrays
----------------------

If you prefer using a parameter array to configure a Dynamic Link, or if this SDK doesn't yet have support for a
given new option, you can pass an array to the ``createDynamicLink()`` method. As the parameters will not be processed
or validated by the SDK, you have to make sure that the parameter structure matches the one described in the
`API Reference Documentation <https://firebase.google.com/docs/reference/dynamic-links/link-shortener>`_

.. code-block:: php

    use use Kreait\Firebase\DynamicLink\CreateDynamicLink\FailedToCreateDynamicLink;

    $parameters = [
        'dynamicLinkInfo' => [
            'domainUriPrefix' => 'https://example.page.link',
            'link' => 'https://domain.tld/some/path',
        ],
        'suffix' => ['option' => 'SHORT'],
    ];

    try {
        $link = $dynamicLinks->createDynamicLink($parameters);
    } catch (FailedToCreateDynamicLink $e) {
        echo $e->getMessage(); exit;
    }
