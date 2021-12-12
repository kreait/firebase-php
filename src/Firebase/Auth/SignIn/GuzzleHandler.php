<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\SignIn;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Query;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Kreait\Firebase\Auth\IsTenantAware;
use Kreait\Firebase\Auth\SignIn;
use Kreait\Firebase\Auth\SignInAnonymously;
use Kreait\Firebase\Auth\SignInResult;
use Kreait\Firebase\Auth\SignInWithCustomToken;
use Kreait\Firebase\Auth\SignInWithEmailAndOobCode;
use Kreait\Firebase\Auth\SignInWithEmailAndPassword;
use Kreait\Firebase\Auth\SignInWithIdpCredentials;
use Kreait\Firebase\Auth\SignInWithRefreshToken;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
final class GuzzleHandler implements Handler
{
    /** @var array<string, mixed> */
    private static array $defaultBody = [
        'returnSecureToken' => true,
    ];

    /** @var array<string, mixed> */
    private static array $defaultHeaders = [
        'Content-Type' => 'application/json; charset=UTF-8',
    ];

    private ClientInterface $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function handle(SignIn $action): SignInResult
    {
        $request = $this->createApiRequest($action);

        try {
            $response = $this->client->send($request, ['http_errors' => false]);
        } catch (GuzzleException $e) {
            throw FailedToSignIn::fromPrevious($e);
        }

        if ($response->getStatusCode() !== 200) {
            throw FailedToSignIn::withActionAndResponse($action, $response);
        }

        try {
            $data = JSON::decode((string) $response->getBody(), true);
        } catch (\InvalidArgumentException $e) {
            throw FailedToSignIn::fromPrevious($e);
        }

        return SignInResult::fromData($data);
    }

    private function createApiRequest(SignIn $action): RequestInterface
    {
        switch (true) {
            case $action instanceof SignInAnonymously:
                return $this->anonymous($action);
            case $action instanceof SignInWithCustomToken:
                return $this->customToken($action);
            case $action instanceof SignInWithEmailAndPassword:
                return $this->emailAndPassword($action);
            case $action instanceof SignInWithEmailAndOobCode:
                return $this->emailAndOobCode($action);
            case $action instanceof SignInWithIdpCredentials:
                return $this->idpCredentials($action);
            case $action instanceof SignInWithRefreshToken:
                return $this->refreshToken($action);
            default:
                throw new FailedToSignIn(self::class.' does not support '.\get_class($action));
        }
    }

    private function anonymous(SignInAnonymously $action): Request
    {
        $uri = Utils::uriFor('https://identitytoolkit.googleapis.com/v1/accounts:signUp');

        $body = Utils::streamFor(JSON::encode(self::prepareBody($action), JSON_FORCE_OBJECT));

        $headers = self::$defaultHeaders;

        return new Request('POST', $uri, $headers, $body);
    }

    private function customToken(SignInWithCustomToken $action): Request
    {
        $uri = Utils::uriFor('https://identitytoolkit.googleapis.com/v1/accounts:signInWithCustomToken');

        $body = Utils::streamFor(JSON::encode(\array_merge(self::prepareBody($action), [
            'token' => $action->customToken(),
        ]), JSON_FORCE_OBJECT));

        $headers = self::$defaultHeaders;

        return new Request('POST', $uri, $headers, $body);
    }

    private function emailAndPassword(SignInWithEmailAndPassword $action): Request
    {
        $uri = Utils::uriFor('https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword');

        $body = Utils::streamFor(JSON::encode(\array_merge(self::prepareBody($action), [
            'email' => $action->email(),
            'password' => $action->clearTextPassword(),
            'returnSecureToken' => true,
        ]), JSON_FORCE_OBJECT));

        $headers = self::$defaultHeaders;

        return new Request('POST', $uri, $headers, $body);
    }

    private function emailAndOobCode(SignInWithEmailAndOobCode $action): Request
    {
        $uri = Utils::uriFor('https://www.googleapis.com/identitytoolkit/v3/relyingparty/emailLinkSignin');

        $body = Utils::streamFor(JSON::encode(\array_merge(self::prepareBody($action), [
            'email' => $action->email(),
            'oobCode' => $action->oobCode(),
            'returnSecureToken' => true,
        ]), JSON_FORCE_OBJECT));

        $headers = self::$defaultHeaders;

        return new Request('POST', $uri, $headers, $body);
    }

    private function idpCredentials(SignInWithIdpCredentials $action): Request
    {
        $uri = Utils::uriFor('https://identitytoolkit.googleapis.com/v1/accounts:signInWithIdp');

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

        $rawBody = \array_merge(self::prepareBody($action), [
            'postBody' => \http_build_query($postBody),
            'returnIdpCredential' => true,
            'requestUri' => $action->requestUri(),
        ]);

        if ($action->linkingIdToken()) {
            $rawBody['idToken'] = $action->linkingIdToken();
        }

        $body = Utils::streamFor(JSON::encode($rawBody, JSON_FORCE_OBJECT));

        $headers = self::$defaultHeaders;

        return new Request('POST', $uri, $headers, $body);
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

        $uri = Utils::uriFor('https://securetoken.googleapis.com/v1/token');

        return new Request('POST', $uri, $headers, $body);
    }

    /**
     * @return array<string, mixed>
     */
    private static function prepareBody(SignIn $action): array
    {
        $body = self::$defaultBody;

        if ($action instanceof IsTenantAware && $tenantId = $action->tenantId()) {
            $body['tenantId'] = $tenantId;
        }

        return $body;
    }
}
