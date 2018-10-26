<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Util;

use Lcobucci\JWT\Signature;
use Lcobucci\JWT\Signer;

final class TestSigner implements Signer
{
    public function getAlgorithmId()
    {
        return 'test';
    }

    public function modifyHeader(array &$headers)
    {
        $headers['alg'] = $this->getAlgorithmId();
    }

    public function sign($payload, $key)
    {
        return new Signature($payload.$key);
    }

    public function verify($expected, $payload, $key)
    {
        return $expected === $payload.$key;
    }
}
