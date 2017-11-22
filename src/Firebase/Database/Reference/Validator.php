<?php

namespace Kreait\Firebase\Database\Reference;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Psr\Http\Message\UriInterface;

class Validator
{
    const MAX_DEPTH = 32;
    const MAX_KEY_SIZE = 768;
    const INVALID_KEY_CHARS = '.$#[]';

    /**
     * Checks the reference URI for invalid properties.
     *
     * @param UriInterface $uri
     *
     * @throws InvalidArgumentException on
     */
    public function validateUri(UriInterface $uri)
    {
        // Firebase has valid REST URL endpoints that contain invalid
        // characters. For example, the PUT .settings/rules endpoint
        // writes rules to the database. This array allows us to filter
        // validation on those technically valid URLs.
        $validUrls = [
            '.settings/rules',
        ];

        $path = trim($uri->getPath(), '/');

        if (!in_array($path, $validUrls)) {
            $this->validateDepth($path);

            $keys = explode('/', $path);

            foreach ($keys as $key) {
                $this->validateKeySize($key);
                $this->validateChars($key);
            }
        }
    }

    private function validateDepth(string $path)
    {
        $depth = substr_count($path, '/') + 1;

        if ($depth > self::MAX_DEPTH) {
            throw new InvalidArgumentException(sprintf(
                'A reference location must not more than %d levels deep, "%s" has %d.',
                self::MAX_DEPTH, $path, $depth
            ));
        }
    }

    private function validateKeySize(string $key)
    {
        if (($length = mb_strlen($key, '8bit')) > self::MAX_KEY_SIZE) {
            throw new InvalidArgumentException(sprintf(
                'A reference\'s child key must not be larger than %d bytes, "%s" has a size of %d bytes.',
                self::MAX_KEY_SIZE, $key, $length
            ));
        }
    }

    private function validateChars($key)
    {
        $key = rawurldecode($key);

        $pattern = sprintf('/[%s]/', preg_quote(self::INVALID_KEY_CHARS, '/'));

        if (preg_match($pattern, $key)) {
            throw new InvalidArgumentException(sprintf(
                'The child key "%s" contains one of the following invalid characters: "%s"',
                $key, self::INVALID_KEY_CHARS
            ));
        }
    }
}
