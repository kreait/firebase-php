<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\SignIn;

use Beste\Json;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Query;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Kreait\Firebase\Auth\AuthResourceUrlBuilder;
use Kreait\Firebase\Auth\IsTenantAware;
use Kreait\Firebase\Auth\SignIn;
use Kreait\Firebase\Auth\SignInAnonymously;
use Kreait\Firebase\Auth\SignInResult;
use Kreait\Firebase\Auth\SignInWithCustomToken;
use Kreait\Firebase\Auth\SignInWithEmailAndOobCode;
use Kreait\Firebase\Auth\SignInWithEmailAndPassword;
use Kreait\Firebase\Auth\SignInWithIdpCredentials;
use Kreait\Firebase\Auth\SignInWithRefreshToken;
use Kreait\Firebase\Util;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use UnexpectedValueException;

use function http_build_query;
use function str_replace;

use const JSON_FORCE_OBJECT;

/**
 * @internal
 */
final class GuzzleHandler
{
    /**
     * @var array<non-empty-string, mixed>
     */
    private static array $defaultBody = [
        'returnSecureToken' => true,
    ];

    /**
     * @var array<non-empty-string, mixed>
     */
    private static array $defaultHeaders = [
        'Content-Type' => 'application/json; charset=UTF-8',
    ];

    public function __construct(
        private readonly string $projectId,
        private readonly ClientInterface $client,
    ) {
    }

    public function handle(SignIn $action): SignInResult
    {
        $request = $this->createApiRequest($action);

        try {
            $response = $this->client->send($request, ['http_errors' => false]);
        } catch (ClientExceptionInterface $e) {
            throw FailedToSignIn::fromPrevious($e);
        }

        if ($response->getStatusCode() !== 200) {
            throw FailedToSignIn::withActionAndResponse($action, $response);
        }

        try {
            $data = Json::decode((string) $response->getBody(), true);
        } catch (UnexpectedValueException $e) {
            throw FailedToSignIn::fromPrevious($e);
        }

        return SignInResult::fromData($data);
    }

    private function createApiRequest(SignIn $action): RequestInterface
    {
        return match (true) {
            $action instanceof SignInAnonymously => $this->anonymous($action),
            $action instanceof SignInWithCustomToken => $this->customToken($action),
            $action instanceof SignInWithEmailAndPassword => $this->emailAndPassword($action),
            $action instanceof SignInWithEmailAndOobCode => $this->emailAndOobCode($action),
            $action instanceof SignInWithIdpCredentials => $this->idpCredentials($action),
            $action instanceof SignInWithRefreshToken => $this->refreshToken($action),
            default => throw new FailedToSignIn(self::class.' does not support '.$action::class),
        };
    }

    private function anonymous(SignInAnonymously $action): Request
    {
        $url = AuthResourceUrlBuilder::create()->getUrl('/accounts:signUp');

        $body = Utils::streamFor(Json::encode($this->prepareBody($action), JSON_FORCE_OBJECT));

        $headers = self::$defaultHeaders;

        return new Request('POST', $url, $headers, $body);
    }

    private function customToken(SignInWithCustomToken $action): Request
    {
        $url = AuthResourceUrlBuilder::create()->getUrl('/accounts:signInWithCustomToken');

        $body = Utils::streamFor(
            Json::encode([...$this->prepareBody($action), 'token' => $action->customToken()], JSON_FORCE_OBJECT),
        );

        $headers = self::$defaultHeaders;

        return new Request('POST', $url, $headers, $body);
    }

    private function emailAndPassword(SignInWithEmailAndPassword $action): Request
    {
        $url = AuthResourceUrlBuilder::create()->getUrl('/accounts:signInWithPassword');

        $body = Utils::streamFor(
            Json::encode([
                ...$this->prepareBody($action),
                'email' => $action->email(),
                'password' => $action->clearTextPassword(),
                'returnSecureToken' => true,
            ], JSON_FORCE_OBJECT),
        );

        $headers = self::$defaultHeaders;

        return new Request('POST', $url, $headers, $body);
    }

    private function emailAndOobCode(SignInWithEmailAndOobCode $action): Request
    {
        $url = AuthResourceUrlBuilder::create()->getUrl('/accounts:signInWithEmailLink');

        $body = Utils::streamFor(
            Json::encode([
                ...$this->prepareBody($action),
                'email' => $action->email(),
                'oobCode' => $action->oobCode(),
                'returnSecureToken' => true,
            ], JSON_FORCE_OBJECT),
        );

        $headers = self::$defaultHeaders;

        return new Request('POST', $url, $headers, $body);
    }

    private function idpCredentials(SignInWithIdpCredentials $action): Request
    {
        $url = AuthResourceUrlBuilder::create()->getUrl('/accounts:signInWithIdp');

        $postBody = [
            'access_token' => $action->accessToken(),
            'id_token' => $action->idToken(),
            'providerId' => $action->provider(),
        ];

        if ($oauthTokenSecret = $action->oauthTokenSecret()) {
            $postBody['oauth_token_secret'] = $oauthTokenSecret;
        }

        if ($rawNonce = $action->rawNonce()) {
            $postBody['nonce'] = $rawNonce;
        }

        $rawBody = [
            ...$this->prepareBody($action),
            'postBody' => http_build_query($postBody),
            'returnIdpCredential' => true,
            'requestUri' => $action->requestUri(),
        ];

        if ($action->linkingIdToken()) {
            $rawBody['idToken'] = $action->linkingIdToken();
        }

        $body = Utils::streamFor(Json::encode($rawBody, JSON_FORCE_OBJECT));

        $headers = self::$defaultHeaders;

        return new Request('POST', $url, $headers, $body);
    }

    private function refreshToken(SignInWithRefreshToken $action): Request
    {
        $body = Query::build([
            'grant_type' => 'refresh_token',
            'refresh_token' => $action->refreshToken(),
        ]);

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ];

        $emulatorHost = Util::authEmulatorHost();

        if ($emulatorHost !== null) {
            // The emulator host requires an api key query parameter.
            $url = str_replace('{host}', $emulatorHost, 'http://{host}/securetoken.googleapis.com/v1/token?key=any');
        } else {
            $url = 'https://securetoken.googleapis.com/v1/token';
        }

        return new Request('POST', $url, $headers, $body);
    }

    /**
     * @return array<non-empty-string, mixed>
     */
    private function prepareBody(SignIn $action): array
    {
        $body = self::$defaultBody;
        $body['targetProjectId'] = $this->projectId;

        if ($action instanceof IsTenantAware && $tenantId = $action->tenantId()) {
            $body['tenantId'] = $tenantId;
        }

        return $body;
    }
}
