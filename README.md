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

### Sample Request

``` php
$client = new onassar\RemoteRequests\Base();
$url = 'https://example.org';
$client->setURL($url);
$response = $client->get() ?? 'Could not load response';
echo $response;
exit(0);
```

### Sample JSON Request

``` php
$client = new onassar\RemoteRequests\Base();
$client->setExpectedResponseContentType('application/json');
$url = 'https://example.org/file.json';
$client->setURL($url);
$arr = $client->get() ?? array();
print_r($arr);
exit(0);
```

### Sample Request Data

``` php
$client = new onassar\RemoteRequests\Base();
$url = 'https://example.org';
$client->setURL($url);
$param = 'value';
$requestData = compact('param');
$client->setRequestData($requestData);
$response = $client->get() ?? 'Could not load response';
echo $response;
exit(0);
```

### Related libraries
- [getstencil/PHP-Iconfinder](https://github.com/getstencil/PHP-Iconfinder)
- [onassar/PHP-Icons8](https://github.com/onassar/PHP-Icons8)
- [onassar/PHP-Pexels](https://github.com/onassar/PHP-Pexels)
- [onassar/PHP-Pixabay](https://github.com/onassar/PHP-Pixabay)
- [onassar/PHP-Unsplash](https://github.com/onassar/PHP-Unsplash)
