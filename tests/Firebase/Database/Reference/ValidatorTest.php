<?php

namespace Tests\Firebase\Database\Reference;

use Firebase\Database\Reference\Validator;
use Firebase\Exception\InvalidArgumentException;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;
use Tests\FirebaseTestCase;

class ValidatorTest extends FirebaseTestCase
{
    /**
     * @var UriInterface
     */
    private $uri;

    /**
     * @var Validator
     */
    private $validator;

    protected function setUp()
    {
        parent::setUp();

        $this->uri = new Uri('http://domain.tld');
        $this->validator = new Validator();
    }

    public function testValidateDepth()
    {
        $uri = $this->uri->withPath(str_pad('', (Validator::MAX_DEPTH + 1) * 2, 'x/'));

        $this->expectException(InvalidArgumentException::class);
        $this->validator->validateUri($uri);
    }

    public function testValidateKeySize()
    {
        $uri = $this->uri->withPath(str_pad('', Validator::MAX_KEY_SIZE + 1, 'x'));

        $this->expectException(InvalidArgumentException::class);
        $this->validator->validateUri($uri);
    }

    public function testValidateChars()
    {
        $invalid = str_shuffle(Validator::INVALID_KEY_CHARS)[0];
        $uri = $this->uri->withPath($invalid);

        $this->expectException(InvalidArgumentException::class);
        $this->validator->validateUri($uri);
    }
}
