<?php

declare(strict_types=1);

namespace Kreait\Firebase\Http;

use GuzzleHttp\Psr7\Response;
use function GuzzleHttp\Psr7\stream_for;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class MultipartResponse extends Response
{
    /** @var StreamInterface[] */
    private $bodies = [];

    /** @var string[] */
    private $headers = [];

    /**
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        parent::__construct($response->getStatusCode(), $response->getHeaders(), null, $response->getProtocolVersion(), $response->getReasonPhrase());
        $body = $response->getBody();
        if (null === $body) {
            $this->withoutHeader('Content-Length');
            $this->withoutHeader('Transfer-Encoding');
        } else {
            foreach (self::parseMultipartBody($body) as $parts) {
                $this->bodies[] = stream_for($parts['body']);
                $this->headers[] = $parts['headers'];
            }
        }
    }

    public function getBody()
    {
        return array_shift($this->bodies);
    }

    public function getHeaders()
    {
        return array_shift($this->headers);
    }

    /**
     * Parses a multipart body into multiple parts.
     *
     * @param StreamInterface $stream
     *
     * @return array
     */
    public static function parseMultipartBody(StreamInterface $stream)
    {
        $parts = [];
        $payload = (string) $stream;
        preg_match('/--(.*)\b/', $payload, $boundary);
        if (!empty($boundary)) {
            $messages = array_filter(array_map('trim', explode($boundary[0], $payload)));
            foreach ($messages as $message) {
                if ($message == '--') {
                    break;
                }
                $headers = [];
                list($header_lines, $body) = explode("\r\n\r\n", $message, 2);
                foreach (explode("\r\n", $header_lines) as $header_line) {
                    list($key, $value) = preg_split('/:\s+/', $header_line, 2);
                    $headers[strtolower($key)] = explode(', ', $value);
                }
                $parts[] = [
                    'headers' => $headers,
                    'body' => $body,
                ];
            }
        }
        return $parts;
    }
}
