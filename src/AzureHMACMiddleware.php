<?php

namespace Mobomo\AzureHmacAuth;

use Psr\Http\Message\RequestInterface;

class AzureHMACMiddleware {

    private $secret = '';

    public function __construct($secret)
    {
        $this->secret = $secret;
    }

    public function __invoke(callable $handler) {
        return function (RequestInterface $request, array $options) use (&$handler) {

            $body = $request->getBody();
            $requestUri = $request->getUri();
            
            $host = $requestUri->getHost();
            $requestPathAndQuery = $requestUri->getPath() . '?' . $requestUri->getQuery();

            $datetime = new \DateTime("now", new \DateTimeZone("UTC"));
            $date = $datetime->format('D, d M Y H:i:s \G\M\T');

            $host = parse_url($requestUri, PHP_URL_HOST);
            $contentHash = self::computeContentHash($body);

            $stringToSign = "POST\n{$requestPathAndQuery}\n{$date};{$host};{$contentHash}";
            $signature = $this->computeSignature($stringToSign);

            $authorizationHeader = "HMAC-SHA256 SignedHeaders=x-ms-date;host;x-ms-content-sha256&Signature={$signature}";

            $request = $request->withHeader(
                'Authorization',
                $authorizationHeader
            );

            $request = $request->withHeader(
                'x-ms-date',
                $date
            );

            $request = $request->withHeader(
                'x-ms-content-sha256',
                $contentHash
            );

            return $handler(
                $request,
                $options
            );
        };
    }

    static function computeContentHash($content) {
        $hashedBytes = hash('sha256', $content, true);
        return base64_encode($hashedBytes);
    }
    
    function computeSignature($stringToSign) {
        $key = base64_decode($this->secret);
        $hashedBytes = hash_hmac('sha256', mb_convert_encoding($stringToSign, 'UTF-8'), $key, true);
        return base64_encode($hashedBytes);
    }
}