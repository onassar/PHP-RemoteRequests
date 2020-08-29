# PHP-RemoteRequests
PHP base class to provide remote object request core functionality (including pagination, rate limit lookups, etc).

# Example `onassar\RemoteObjectRequest\Base` requests

``` php
$client = new onassar\RemoteRequests\Base();
$client->setExpectedResponseFormat('plain/text');
$url = 'https://google.com';
$client->setURL($url);
$utm = 'campaign-name';
$requestData = compact('utm');
$client->setRequestData($requestData);
$response = $client->get() ?? 'Could not load response';
echo $response;
exit(0);
```
