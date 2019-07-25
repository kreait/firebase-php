<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests;

use Kreait\Firebase\ServiceAccount;
use PHPUnit\Framework\MockObject\MockObject;

abstract class UnitTestCase extends FirebaseTestCase
{
    /**
     * @return ServiceAccount|MockObject
     */
    protected function createServiceAccountMock()
    {
        $mock = $this->createMock(ServiceAccount::class);

        $mock
            ->method('getProjectId')
            ->willReturn('project');

        $mock
            ->method('getClientId')
            ->willReturn('client');

        $mock
            ->method('getClientEmail')
            ->willReturn('client@email.tld');

        $mock
            ->method('getPrivateKey')
            ->willReturn('-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQC9fUon04iPvcWzwNwMLkiNQizaid+DaUdyv759FfbRxfj7hTWT
BwWxdPMRc7bVGcaPSD4ZzTB8EyiwjfZw5FuunHhC90ihtRS3CULrcLOtZdyY0OXo
dLyg0+r74hI64DAUUm8Q3XE81ZMpZD8ZX12EsjGH6cWkSlW0/q8NsL6llwIDAQAB
AoGBALFkXozEOl8esLuj/BynI6KiZe09D3Mtlwa0vLbLXiJqLLoCrfHzq//CVV9s
Lah4FevDHOf4sMAnC3ulmyV6ktxavVi0cAFN9VRg/Rqszh8FO5FfPqYLmC+ruEBq
/lun5f7JG66SUIGlN5QsewqFISAwEOWA+4IN6zsUdrlbo1shAkEA5cMToo8osqKO
WNbuEb+HmxstHNpdbpM/6Lmp3TVtebH/9KtnDT/XkrlSC5GBqkRDOxslP7XIzZPR
+yzzok4VsQJBANMg335p9g/Q/vl+fxrhBXHaKm2mRO+yDeoA+OEWeRRz6Y0RHKFI
12cwX+XZEiJc+azb68vmxzk5Gfavy+ClmccCQH7gUJFt+I1ckrqgRWrrlxih0zGh
rAKJsbrz+8c537BaCPu1QvzgCkztpU7aFP5PH8kd3l3mJnLPdB793bP85qECQQCp
sk5xCTIh3FZUqvv22s7JiBV6NJ5MGs1cPJPON4Xyjog2Pn7IlAeuhQ9Pa35L6Hc2
HT4VkdSnheH8iahRVEmZAkBRGoQz5HSLMfyb/zsxpCrUZv886exZ/tGEhuY3s1j6
npI3CJzFaivN28EWmBJI4/pJtTATlNsKfLxCKUvCJ55k
-----END RSA PRIVATE KEY-----');

        return $mock;
    }
}
