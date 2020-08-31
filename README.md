# PHP-RemoteRequests
PHP HTTP request class (and traits) to provide remote request functionality
(including pagination, rate limit lookups and standard 3rd-party search API methods).

Features include:
- Requests using cURL
- Requests using streams
- Recursive requests
- Rate limit access
- Multi-attempts (with sleep calls between attempts)
- Header access
- JSON parsing

### Example `onassar\RemoteObjectRequest\Base` requests

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
