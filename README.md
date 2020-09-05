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
- `POST` requests (currently limited to `stream` requests)

### Requires
- [PHP-RiskyClosure](https://github.com/onassar/PHP-RiskyClosure)

### Sample Request

``` php
$client = new onassar\RemoteRequests\Base();
$url = 'https://example.org';
$client->setURL($url);
$response = $client->get() ?? 'Could not load response';
echo $response;
exit(0);
```

### Sample Request (short)

``` php
$client = new onassar\RemoteRequests\Base();
$response = $client->get('https://example.org/') ?? 'Could not load response';
echo $response;
exit(0);
```

***

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

### Sample JSON Request (short)

``` php
$client = new onassar\RemoteRequests\Base();
$client->setExpectedResponseContentType('application/json');
$arr = $client->get('https://example.org/file.json') ?? array();
print_r($arr);
exit(0);
```

***

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

### Sample Request Data (short)

``` php
$client = new onassar\RemoteRequests\Base();
$param = 'value';
$requestData = compact('param');
$client->setRequestData($requestData);
$response = $client->get('https://example.org/') ?? 'Could not load response';
echo $response;
exit(0);
```

***

### Sample cURL Request

``` php
$client = new onassar\RemoteRequests\Base();
$client->setRequestApproach('cURL');
$url = 'https://example.org';
$client->setURL($url);
$response = $client->get() ?? 'Could not load response';
echo $response;
exit(0);
```

### Sample cURL Request (short)

``` php
$client = new onassar\RemoteRequests\Base();
$client->setRequestApproach('cURL');
$response = $client->get('https://example.org') ?? 'Could not load response';
echo $response;
exit(0);
```

### Todo
- Support `POST` calls using the `cURL` request approach

### Related libraries
- [getstencil/PHP-Iconfinder](https://github.com/getstencil/PHP-Iconfinder)
- [onassar/PHP-Bitly](https://github.com/onassar/PHP-Bitly)
- [onassar/PHP-Icons8](https://github.com/onassar/PHP-Icons8)
- [onassar/PHP-Pexels](https://github.com/onassar/PHP-Pexels)
- [onassar/PHP-Pixabay](https://github.com/onassar/PHP-Pixabay)
- [onassar/PHP-Unsplash](https://github.com/onassar/PHP-Unsplash)
