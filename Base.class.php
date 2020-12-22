<?php

    // Namespace overhead
    namespace onassar\RemoteRequests;
    use onassar\RiskyClosure;

    /**
     * Base
     * 
     * Class for basic cURL and stream requests.
     * 
     * @link    https://github.com/onassar/PHP-RemoteRequests
     * @author  Oliver Nassar <onassar@gmail.com>
     */
    class Base
    {
        /**
         * _debugMode
         * 
         * @access  protected
         * @var     bool (default: false)
         */
        protected $_debugMode = false;

        /**
         * _expectedResponseContentType
         * 
         * @access  protected
         * @var     string (default: 'plain/text')
         */
        protected $_expectedResponseContentType = 'plain/text';

        /**
         * _failedAttemptDelay
         * 
         * @access  protected
         * @var     int (default: 2000)
         */
        protected $_failedAttemptDelay = 2000;

        /**
         * _failedAttemptLoggingEvaluator
         * 
         * @access  protected
         * @var     null|callable (default: null)
         */
        protected $_failedAttemptLoggingEvaluator = null;

        /**
         * _ignoreErrors
         * 
         * @access  protected
         * @var     bool (default: true)
         */
        protected $_ignoreErrors = true;

        /**
         * _lastRemoteRequestHeaders
         * 
         * @access  protected
         * @var     array (default: array())
         */
        protected $_lastRemoteRequestHeaders = array();

        /**
         * _logFunction
         * 
         * @access  protected
         * @var     null|callable (default: null)
         */
        protected $_logFunction = null;

        /**
         * _maxAttempts
         * 
         * @access  protected
         * @var     int (default: 2)
         */
        protected $_maxAttempts = 2;

        /**
         * _parsedResponse
         * 
         * The last response, parsed depending on the content type defined for
         * the remote request.
         * 
         * @access  protected
         * @var     mixed (default: null)
         */
        protected $_parsedResponse = null;

        /**
         * _postContent
         * 
         * @access  protected
         * @var     null|string (default: null)
         */
        protected $_postContent = null;

        /**
         * _quiet
         * 
         * Whether or not messages should be logged to the system when closures
         * fail.
         * 
         * @access  protected
         * @var     bool (default: false)
         */
        protected $_quiet = false;

        /**
         * _requestApproach
         * 
         * @access  protected
         * @var     string (default: 'streams')
         */
        protected $_requestApproach = 'streams';

        /**
         * _requestData
         * 
         * @access  protected
         * @var     array (default: array())
         */
        protected $_requestData = array();

        /**
         * _requestMethod
         * 
         * @access  protected
         * @var     string (default: 'get')
         */
        protected $_requestMethod = 'get';

        /**
         * _requestTimeout
         * 
         * @access  protected
         * @var     int (default: 10)
         */
        protected $_requestTimeout = 10;

        /**
         * _riskyClosure
         * 
         * This stores a reference to the RiskyClosure\Base object that gets
         * created during the attempt flow for remote requests. At the time of
         * documentation, it's only used by during the trace logging flow.
         * 
         * @access  protected
         * @var     null|RiskyClosure\Base (default: null)
         */
        protected $_riskyClosure = null;

        /**
         * _streamOptions
         * 
         * @access  protected
         * @var     array (default: array())
         */
        protected $_streamOptions = array();

        /**
         * _traceLogFunction
         * 
         * @access  protected
         * @var     null|callable (default: null)
         */
        protected $_traceLogFunction = null;

        /**
         * _url
         * 
         * @access  protected
         * @var     null|string (default: null)
         */
        protected $_url = null;

        /**
         * __construct
         * 
         * @access  public
         * @return  void
         */
        public function __construct()
        {
        }

        /**
         * _addURLParams
         * 
         * @access  protected
         * @param   string $url
         * @param   array $params
         * @return  string
         */
        protected function _addURLParams(string $url, array $params): string
        {
            if (empty($params) === true) {
                return $url;
            }
            $queryString = http_build_query($params);
            $piece = parse_url($url, PHP_URL_QUERY);
            if ($piece === null) {
                $url = ($url) . '?' . ($queryString);
                return $url;
            }
            $url = ($url) . '&' . ($queryString);
            return $url;
        }

        /**
         * _attempt
         * 
         * Uses onassar\RiskyClosure\Base to facilitate closure attempts with
         * sleep, max attempt and log closure properties.
         * 
         * @access  protected
         * @param   \Closure $closure
         * @return  null|string
         */
        protected function _attempt(\Closure $closure): ?string
        {
            $riskyClosure = new RiskyClosure\Base($closure);
            $this->_riskyClosure = $riskyClosure;
            $failedAttemptDelay = $this->_failedAttemptDelay;
            $failedAttemptLoggingEvaluator = $this->_failedAttemptLoggingEvaluator;
            $logFunction = array($this, 'log');
            $maxAttempts = $this->_maxAttempts;
            $traceLogFunction = array($this, 'logTrace');
            $riskyClosure->setFailedAttemptDelay($failedAttemptDelay);
            $riskyClosure->setFailedAttemptLoggingEvaluator($failedAttemptLoggingEvaluator);
            $riskyClosure->setLogFunction($logFunction);
            $riskyClosure->setMaxAttempts($maxAttempts);
            $riskyClosure->setTraceLogFunction($traceLogFunction);
            list($exception, $response) = $riskyClosure->attempt();
            return $response;
        }

        /**
         * _debugModeLog
         * 
         * @access  protected
         * @param   array $values,...
         * @return  bool
         */
        protected function _debugModeLog(... $values): bool
        {
            $debugMode = $this->_debugMode;
            if ($debugMode === false) {
                return false;
            }
            $this->log(... $values);
            return true;
        }

        /**
         * _getCURLRequestHeaders
         * 
         * @access  protected
         * @return  array
         */
        protected function _getCURLRequestHeaders(): array
        {
            $headers = array();
            return $headers;
        }

        /**
         * _getParsedJSONResponse
         * 
         * @access  protected
         * @param   null|string $response
         * @return  null|array
         */
        protected function _getParsedJSONResponse(?string $response): ?array
        {
            if ($response === null) {
                return null;
            }
            $expectedResponseContentType = $this->_expectedResponseContentType;
            if ($expectedResponseContentType !== 'application/json') {
                return null;
            }
            json_decode($response);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }
            $response = json_decode($response, true);
            return $response;
        }

        /**
         * _getParsedTextResponse
         * 
         * @access  protected
         * @param   null|string $response
         * @return  null|string
         */
        protected function _getParsedTextResponse(?string $response): ?string
        {
            if ($response === null) {
                return null;
            }
            $expectedResponseContentType = $this->_expectedResponseContentType;
            if ($expectedResponseContentType !== 'plain/text') {
                return null;
            }
            return $response;
        }

        /**
         * _getPOSTContent
         * 
         * @access  protected
         * @return  null|string
         */
        protected function _getPOSTContent(): ?string
        {
            $postContent = $this->_postContent;
            return $postContent;
        }

        /**
         * _getRequestData
         * 
         * @access  protected
         * @return  array
         */
        protected function _getRequestData(): array
        {
            $requestData = $this->_requestData;
            return $requestData;
        }

        /**
         * _getRequestMethod
         * 
         * @access  protected
         * @return  string
         */
        protected function _getRequestMethod(): string
        {
            $requestMethod = $this->_requestMethod;
            $requestMethod = strtoupper($requestMethod);
            return $requestMethod;
        }

        /**
         * _getRequestStreamContext
         * 
         * @access  protected
         * @return  resource
         */
        protected function _getRequestStreamContext()
        {
            $options = $this->_getRequestStreamContextOptions();
            $streamContext = stream_context_create($options);
            return $streamContext;
        }

        /**
         * _getRequestStreamContextOptions
         * 
         * @access  protected
         * @return  array
         */
        protected function _getRequestStreamContextOptions(): array
        {
            $ignoreErrors = $this->_ignoreErrors;
            $debugMode = $this->_debugMode;
            if ($debugMode === true) {
                $ignoreErrors = false;
            }
            $requestMethod = $this->_getRequestMethod();
            $requestTimeout = $this->_requestTimeout;
            $options = array(
                'http' => array(
                    'ignore_errors' => $ignoreErrors,
                    'method' => $requestMethod,
                    'timeout' => $requestTimeout
                )
            );
            if ($requestMethod === 'POST') {
                $options['http']['content'] = $this->_getPOSTContent();
            }
            $options = array_merge($options, $this->_streamOptions);
            return $options;
        }

        /**
         * _getRequestURL
         * 
         * Returns the URL that ought to be requested, which will be modified
         * depending on whether or not any request data was set.
         * 
         * @access  protected
         * @return  string
         */
        protected function _getRequestURL(): string
        {
            $url = $this->_url;
            $requestData = $this->_getRequestData();
            $url = $this->_addURLParams($url, $requestData);
            return $url;
        }

        /**
         * _getURL
         * 
         * @access  protected
         * @return  null|mixed
         */
        protected function _getURLResponse()
        {
            $response = $this->_requestURL();
            $response = $this->_getParsedTextResponse($response) ?? $this->_getParsedJSONResponse($response) ?? null;
            $this->_parsedResponse = $response;
            return $response;
        }

        /**
         * _parseCURLResponse
         * 
         * This method was required because at times the cURL requests would not
         * return the headers, which would cause issues.
         * 
         * @access  protected
         * @param   string $response
         * @return  array
         */
        protected function _parseCURLResponse(string $response): array
        {
            $delimiter = "\r\n\r\n";
            $pieces = explode($delimiter, $response);
            if (count($pieces) === 1) {
                $headers = '';
                $body = $response;
                $response = array($headers, $body);
                return $response;
            }
            list($headers, $body) = explode("\r\n\r\n", $response, 2);
            $response = array($headers, $body);
            return $response;
        }

        /**
         * _requestURL
         * 
         * @throws  \Exception
         * @access  protected
         * @return  null|string
         */
        protected function _requestURL(): ?string
        {
            if ($this->_requestApproach === 'cURL') {
                $response = $this->_requestURLUsingCURL();
                return $response;
            }
            if ($this->_requestApproach === 'streams') {
                $response = $this->_requestURLUsingStreams();
                return $response;
            }
            $msg = 'Invalid request approach';
            throw new \Exception($msg);
        }

        /**
         * _requestURLUsingCURL
         * 
         * @see     https://stackoverflow.com/a/9183272/115025
         * @see     https://stackoverflow.com/a/3987037/115025
         * @access  protected
         * @return  null|string
         */
        protected function _requestURLUsingCURL(): ?string
        {
            $closure = function() {
                $url = $this->_getRequestURL();
                $this->_debugModeLog('cURL', $url);
                $requestTimeout = $this->_requestTimeout;
                $headers = $this->_getCURLRequestHeaders();
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $requestTimeout);
                curl_setopt($ch, CURLOPT_TIMEOUT, $requestTimeout);
                curl_setopt($ch, CURLOPT_HEADER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                $code = curl_errno($ch);
                if ($code > 0) {
                    $msg = curl_error($ch);
                    throw new \Exception($msg, $code);
                }
                curl_close($ch);
                return $response;
            };
            $response = $this->_attempt($closure);
            if ($response === false) {
                return null;
            }
            if ($response === null) {
                return null;
            }
            list($headers, $body) = $this->_parseCURLResponse($response);
            $this->_setCURLResponseHeaders($headers);
            return $body;
        }

        /**
         * _requestURLUsingStreams
         * 
         * @see     http://php.net/manual/en/reserved.variables.httpresponseheader.php
         * @access  protected
         * @return  null|string
         */
        protected function _requestURLUsingStreams(): ?string
        {
            $closure = function() {
                $url = $this->_getRequestURL();
                $this->_debugModeLog('stream', $url);
                $streamContext = $this->_getRequestStreamContext();
                $response = file_get_contents($url, false, $streamContext);
                $this->_lastRemoteRequestHeaders = $http_response_header ?? $this->_lastRemoteRequestHeaders;
                return $response;
            };
            $response = $this->_attempt($closure);
            if ($response === false) {
                return null;
            }
            if ($response === null) {
                return null;
            }
            return $response;
        }

        /**
         * _setCURLResponseHeaders
         * 
         * @access  protected
         * @param   string $headers
         * @return  void
         */
        protected function _setCURLResponseHeaders(string $headers): void
        {
            $headers = explode("\n", $headers);
            $this->_lastRemoteRequestHeaders = $headers;
        }

        /**
         * _setPOSTContent
         * 
         * @access  protected
         * @param   string $postContent
         * @return  void
         */
        protected function _setPOSTContent(string $postContent): void
        {
            $this->_postContent = $postContent;
        }

        /**
         * _sleep
         * 
         * @access  protected
         * @param   int $duration in milliseconds
         * @return  void
         */
        protected function _sleep(int $duration): void
        {
            usleep($duration * 1000);
        }

        /**
         * get
         * 
         * @throws  \Exception
         * @access  public
         * @param   null|string $url (default: null)
         * @return  mixed
         */
        public function get(?string $url = null)
        {
            $url = $url ?? $this->_url ?? null;
            if ($url === null) {
                $msg = '$url not set';
                throw new \Exception($msg);
            }
            $this->setURL($url);
            $response = $this->_getURLResponse();
            return $response;
        }

        /**
         * getFormattedHeaders
         * 
         * Returns a hash of the headers. The complicated nature of colon (:)
         * lookups is because some headers have colons in multiple places (eg.
         * date strings).
         * 
         * @see     https://i.imgur.com/ceCXWJs.png
         * @access  public
         * @return  array
         */
        public function getFormattedHeaders(): array
        {
            $headers = $this->_lastRemoteRequestHeaders ?? array();
            $formattedHeaders = array();
            foreach ($headers as $header) {
                $pieces = explode(':', $header);
                if (count($pieces) === 1) {
                    continue;
                }
                if (count($pieces) === 2) {
                    $key = trim($pieces[0]);
                    $value = trim($pieces[1]);
                    $formattedHeaders[$key] = $value;
                    continue;
                }
                $key = array_shift($pieces);
                $key = trim($key);
                $value = implode(':', $pieces);
                $value = trim($value);
                $formattedHeaders[$key] = $value;
            }
            return $formattedHeaders;
        }

        /**
         * getHeaders
         * 
         * @access  public
         * @return  array
         */
        public function getHeaders(): array
        {
            $headers = $this->_lastRemoteRequestHeaders ?? array();
            return $headers;
        }

        /**
         * getParsedResponse
         * 
         * @access  public
         * @return  mixed
         */
        public function getParsedResponse()
        {
            $parsedResponse = $this->_parsedResponse;
            return $parsedResponse;
        }

        /**
         * log
         * 
         * Method which handles logging of any messaging associated with the
         * remote request. It ought to be public to ensure it works well with
         * onassar\RiskyClosure\Base.
         * 
         * @access  public
         * @param   array $values,...
         * @return  bool
         */
        public function log(... $values): bool
        {
            if ($this->_quiet === true) {
                return false;
            }
            if ($this->_logFunction === null) {
                foreach ($values as $value) {
                    error_log($value);
                }
                return false;
            }
            $closure = $this->_logFunction;
            $args = $values;
            call_user_func_array($closure, $args);
            return true;
        }

        /**
         * logTrace
         * 
         * @access  public
         * @param   array $trace
         * @return  bool
         */
        public function logTrace(array $trace): bool
        {
            if ($this->_quiet === true) {
                return false;
            }
            if ($this->_traceLogFunction === null) {
                $trace = implode("\n", $trace);
                error_log($trace);
                return false;
            }
            $closure = $this->_traceLogFunction;
            $args = array($trace, $this->_riskyClosure);
            call_user_func_array($closure, $args);
            return true;
        }

        /**
         * mergeRequestData
         * 
         * @access  public
         * @param   array $values,...
         * @return  void
         */
        public function mergeRequestData(... $values): void
        {
            $requestData = $this->_requestData;
            foreach ($values as $value) {
                $requestData = array_merge($requestData, $value);
            }
            $this->setRequestData($requestData);
        }

        /**
         * setAttemptClosure
         * 
         * @access  public
         * @param   \Closure $attemptClosure
         * @return  void
         */
        public function setAttemptClosure(\Closure $attemptClosure): void
        {
            $this->_attemptClosure = $attemptClosure;
        }

        /**
         * setDebugMode
         * 
         * @access  public
         * @param   bool $debugMode
         * @return  void
         */
        public function setDebugMode(bool $debugMode): void
        {
            $this->_debugMode = $debugMode;
        }

        /**
         * setExpectedResponseContentType
         * 
         * @access  public
         * @param   string $expectedResponseContentType
         * @return  void
         */
        public function setExpectedResponseContentType(string $expectedResponseContentType): void
        {
            $this->_expectedResponseContentType = $expectedResponseContentType;
        }

        /**
         * setFailedAttemptDelay
         * 
         * @access  public
         * @param   int $failedAttemptDelay
         * @return  void
         */
        public function setFailedAttemptDelay(int $failedAttemptDelay): void
        {
            $this->_failedAttemptDelay = $failedAttemptDelay;
        }

        /**
         * setFailedAttemptLoggingEvaluator
         * 
         * @access  public
         * @param   null|callable $callback
         * @return  void
         */
        public function setFailedAttemptLoggingEvaluator(?callable $callback): void
        {
            $this->_failedAttemptLoggingEvaluator = $callback;
        }

        /**
         * setLogFunction
         * 
         * @access  public
         * @param   callable $logFunction
         * @return  void
         */
        public function setLogFunction(callable $logFunction): void
        {
            $this->_logFunction = $logFunction;
        }

        /**
         * setMaxAttempts
         * 
         * @access  public
         * @param   int $maxAttempts
         * @return  void
         */
        public function setMaxAttempts(int $maxAttempts): void
        {
            $this->_maxAttempts = $maxAttempts;
        }

        /**
         * setQuiet
         * 
         * @access  public
         * @param   null|bool $quiet
         * @return  void
         */
        public function setQuiet(?bool $quiet): void
        {
            $this->_quiet = $quiet ?? $this->_quiet;
        }

        /**
         * setRequestApproach
         * 
         * @access  public
         * @param   string $requestApproach
         * @return  void
         */
        public function setRequestApproach(string $requestApproach): void
        {
            $this->_requestApproach = $requestApproach;
        }

        /**
         * setRequestData
         * 
         * @access  public
         * @param   array $requestData
         * @return  void
         */
        public function setRequestData(array $requestData): void
        {
            $this->_requestData = $requestData;
        }

        /**
         * setRequestDataValue
         * 
         * @access  public
         * @param   string $key
         * @param   mixed $value
         * @return  void
         */
        public function setRequestDataValue(string $key, $value): void
        {
            $this->_requestData[$key] = $value;
        }

        /**
         * setRequestMethod
         * 
         * @access  public
         * @param   string $requestMethod
         * @return  void
         */
        public function setRequestMethod(string $requestMethod): void
        {
            $requestMethod = strtolower($requestMethod);
            $this->_requestMethod = $requestMethod;
        }

        /**
         * setRequestTimeout
         * 
         * Sets the timeout for requests (for both cURL and stream approaches)
         * in seconds.
         * 
         * @access  public
         * @param   int $requestTimeout
         * @return  void
         */
        public function setRequestTimeout(int $requestTimeout): void
        {
            $this->_requestTimeout = $requestTimeout;
        }

        /**
         * setStreamOptions
         * 
         * @access  public
         * @param   array $streamOptions
         * @return  void
         */
        public function setStreamOptions(array $streamOptions): void
        {
            $this->_streamOptions = $streamOptions;
        }

        /**
         * setTraceLogFunction
         * 
         * @access  public
         * @param   callable $traceLogFunction
         * @return  void
         */
        public function setTraceLogFunction(callable $traceLogFunction): void
        {
            $this->_traceLogFunction = $traceLogFunction;
        }

        /**
         * setURL
         * 
         * @access  public
         * @param   null|string $url
         * @return  void
         */
        public function setURL(?string $url): void
        {
            $this->_url = $url;
        }
    }
