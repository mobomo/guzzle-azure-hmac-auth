# Usage

```
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use stevenlafl\AzureHmacAuth\AzureHMACMiddleware;

$azureHMACMiddleware = new AzureHMACMiddleware('<secret key>');

$handlerStack = HandlerStack::create();
$handlerStack->push($azureHMACMiddleware, 'hmac-auth');

$resourceEndpoint = "https://<hostname>.communication.azure.com";
$requestPath = "/emails:send?api-version=2023-03-31";
$requestUri = "{$resourceEndpoint}{$requestPath}";

$serializedBody = "<json>";

$client = new Client([
    'handler' => $handlerStack,
]);
$requestMessage = new Request(
    'POST',
    $requestUri,
    array(
        'Content-Type' => 'application/json',
    ),
    $serializedBody
);

$response = $client->send($requestMessage);
```